clearTimeout(PageRefresh);

const pb = new PocketBase("https://api.xlx299.nz");
const coll = "activity";
const topic = "*";
const staleSecs = 600;
var colors = {};

function removeStale() {
  let old = Date.now() - staleSecs * 1000;
  $('li').each(function (i) {
    let ts = $(this).attr('data-ts');
    if (ts && ts < old) {
      $(this).remove();
    }
  });
}

function activity(call, module, ts, duration) {
  duration = duration || 0;
  $('#mod-' + module).css('background-color', call ? 'salmon' : colors[module]);
  if (call) {
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
  }/* else {
    $('#act-' + module + '>span').fadeOut(4000);
  } */
}

async function doRecent() {
  console.log('Recent');
  let result = await pb.collection(coll).getFullList(400,
    { filter: `system = "299" && ts >= ${Date.now() - staleSecs * 1000}` });
  for (let row of result) {
    console.log(row.call, row.module, row.ts);
    activity(row.call, row.module, row.ts, 0);
  }
}

function saveColors() {
  for (m = 0; m < 26; m++) {
    l = String.fromCharCode(m + 'A'.charCodeAt(0));
    colors[l] = jQuery('#mod-' + l).css('background-color');
  }
}

function subscribe() {
  console.log(`Subscribing to ${topic} on ${coll}`);
  pb.collection(coll).subscribe(topic, function (e) {
    console.log(e.record.module, e.record.call, e);
    if (e.record.system == "299") {
      activity(e.record.call, e.record.module, e.record.ts, 400);
    }
  });
  /*.catch((error) => {
    console.error(error);
}); */
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
