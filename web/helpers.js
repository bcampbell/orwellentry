
function hideShow(sel,initialShown,desc) {
    var targs = document.querySelectorAll(sel);
    if (targs.length<1) {
            return;
    }
    for (var i = 0; i < targs.length; i++) {
        if (i<initialShown) {
            continue;
        }
        var el = targs[i];
        el.style.display = 'none';
    }

    var last = targs[targs.length-1];

    last.insertAdjacentHTML('afterend', '<a href="#" class="">' + desc + '</a>');
    var moreButt = last.nextElementSibling;
    moreButt.addEventListener("click",function(evt) {
        evt.preventDefault();
        // find and show first hidden one
        var i;
        for(i=0; i<targs.length; i++ ) {
            if (targs[i].style.display=='none') {
                break;
            }
        }
        if (i<targs.length) {
            targs[i].style.display = '';
        }
        if (i>=(targs.length-1)) {
            // no more to show
            moreButt.style.display = 'none';
        }
    }, false);
}

