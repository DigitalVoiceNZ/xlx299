const pb = new PocketBase("https://api.xlx299.nz");
const coll = "activity";
const topic = "*";
const stalemSecs = 900 * 1000;
var colors = {};

function removeStale() {
  let old = Date.now() - stalemSecs;
  let removed = false;
  $('li').each(function (i) {
    let ts = $(this).attr('data-ts');
    if (ts && ts < old) {
      $ul = $(this).parent();
      $(this).remove();
      // failsafe: if removed last li, nobody is transmitting
      if ($ul.find('li').length == 0) {
        $ul.parent().parent().hide("slow");
      }
    }
  });
  pb.health.check().then(function (result) {
    console.log('healthy');
  }).catch(function (err) {
    console.log('ill', err);
  });
}

function activity(call, module, ts, tsoff, duration) {
  duration = duration || 0;
  $('#mod-' + module).show("slow");
  //$('#mod-' + module).css('background-color', call ? 'salmon' : colors[module]);
  if (call && (tsoff == 0)) {
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
    $ul.prepend('<li style="display: none; background-color: salmon;" data-ts="' + ts + '">' + call + '</li>');
    $ul.find('li:first').slideDown(duration);
  } else {
    $('#act-' + module + ' >li').css('background-color', 'white')
  }
}

async function doRecent() {
  let result = await pb.collection(coll).getFullList(400,
    { filter: `system = "299" && ts >= ${Date.now() - stalemSecs}` });
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
  pb.collection(coll).subscribe(topic, function (e) {
    let old = Date.now() - stalemSecs;
    if ((e.record.system == "299") && (e.record.ts > old)) {
      activity(e.record.call, e.record.module, e.record.ts, 
        e.record.tsoff, 400);
    }
  }).catch((error) => {
    console.log('subscription error');
    console.error(error);
  });
}

var waitForJQuery = setInterval(function () {
  if (window.jQuery) {
    clearInterval(waitForJQuery);

    saveColors();   // save original row colors
    doRecent();     // set the initial state
    subscribe();    // subscribe to changes
    setInterval(removeStale, 60 * 1000);
  } else {
  }
}, 10);
