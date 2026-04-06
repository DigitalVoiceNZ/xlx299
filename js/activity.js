clearTimeout(PageRefresh);

const apiBase = "https://api.xlx299.nz";
const stalemSecs = 900 * 1000;
var colors = {};

function removeStale() {
  let old = Date.now() - stalemSecs;
  $('li').each(function (i) {
    let ts = $(this).attr('data-ts');
    if (ts && ts < old) {
      $ul = $(this).parent();
      $(this).remove();
      // failsafe: if removed last li, nobody is transmitting
      if ($ul.find('li').length == 0) {
        let mid = $ul.parents('tr').attr('id');
        console.log(`failsafe: ${mid}`);
        $('#' + mid).css('background-color', colors[mid.slice(-1)]);
      }
    }
  });
}

function activity(call, module, ts, tsoff, duration) {
  duration = duration || 0;
  let onair = call && (tsoff == 0);
  $('#mod-' + module).css('background-color', onair ? 'salmon' : colors[module]);
  if (onair) {
    let $ul = $('#act-' + module);
    // if first li is the same call, all that is really
    // required is cancel animation and update ts

    // remove existing call
    $ul.find('li').each(function (i) {
      if ($(this).text() == call) {
        $(this).slideUp(duration, function () {
          $(this).remove();
        });
      }
    });
    // add it to the top of the list
    $ul.prepend('<li style="display: none;" data-ts="' + ts + '">' + call + '</li>');
    $ul.find('li:first').slideDown(duration);
  }
}

async function doRecent() {
  const result = await fetch(`${apiBase}/api/activity/recent`).then(r => r.json());
  for (let row of result) {
    activity(row.call, row.module, row.ts, 0,         0);
    activity(row.call, row.module, row.ts, row.tsoff, 0);
  }
}

function saveColors() {
  for (m = 0; m < 26; m++) {
    l = String.fromCharCode(m + 'A'.charCodeAt(0));
    colors[l] = jQuery('#mod-' + l).css('background-color');
  }
}

function subscribe() {
  console.log(`Subscribing to ${apiBase}/api/activity/events`);
  const es = new EventSource(`${apiBase}/api/activity/events`);
  es.onmessage = function (e) {
    const evt = JSON.parse(e.data);
    let old = Date.now() - stalemSecs;
    if ((evt.record.system == "299") && (evt.record.ts > old)) {
      activity(evt.record.call, evt.record.module, evt.record.ts,
        evt.record.tsoff, 400);
    }
  };
  es.onerror = function (err) {
    console.error('SSE error', err);
  };
}

var waitForJQuery = setInterval(function () {
  if (window.jQuery) {
    clearInterval(waitForJQuery);

    saveColors();   // save original row colors
    doRecent();     // set the initial state
    subscribe();    // subscribe to changes
    setInterval(removeStale, 60 * 1000);
  } else {
    console.log('waiting for JQuery');
  }
}, 10);
