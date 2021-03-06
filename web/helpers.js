
function selOrOther(selid,otherid,selotherval) {
    var select = document.getElementById(selid);
    var other = document.getElementById(otherid);
    var f=function(){
        if(select.value==selotherval){
        other.style.display="block";
        } else {
        other.style.display="none";
        }
    }
    select.onchange=f;
    f();
}


function hideShow(sel,initialShown,desc) {
    if(document.addEventListener === undefined) {
        return;
    }

    var targs = document.querySelectorAll(sel);
    if (targs.length<1) {
            return;
    }
    for (var i = 0; i < targs.length; i++) {
        if (i<initialShown) {
            continue;
        }
        // hide any which are empty
        var el = targs[i];
        var inps = el.querySelectorAll("input");
        var used = false;

        for (var j =0;j<inps.length; j++) {
            var inp = inps[j];
            if (inp.parentNode.classList) {
                if(inp.parentNode.classList.contains('fld-error')) {
                    used = true;
                }
            }

            if(inps[j].value != "") {
                used=true;
            }
        }
        if (!used) {
            el.style.display = 'none';
        }
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

// file upload shenanigans
function fancyUpload(targ) {
    if (window.FormData===undefined || window.File===undefined || window.FileList===undefined ) {
        return;
    }


    // augment HTML with extra UI elements
    //
    var extras = '<div class="uploader-progress"><progress max="100"></progress><button class="uploader-progress-cancel">cancel</button><span class="uploader-progress-msg"></span></div>' +
        '<div class="uploader-error"></div>' +
        '<div class="uploader-attached"><div class="uploader-attached-details"></div><button class="uploader-attached-remove">remove</button></div>';

    targ.insertAdjacentHTML('beforebegin','<div class="uploader">' + extras + '</div>');
    var uploader = targ.previousElementSibling;
    var progressUI = uploader.querySelector(".uploader-progress");
    var attachedUI = uploader.querySelector(".uploader-attached");
    var errorUI = targ.parentNode.querySelector(".errorlist li");
    if (errorUI===null) {
        targ.parentNode.insertAdjacentHTML('beforeend','<ul class="errorlist"><li></li></ul>');
        errorUI = targ.parentNode.querySelector(".errorlist li");
    }

    var progress = progressUI.querySelector("progress");
    var progressMsg = progressUI.querySelector(".uploader-progress-msg");
    var cancel = progressUI.querySelector(".uploader-progress-cancel");

    var details = attachedUI.querySelector(".uploader-attached-details");
    var remove = attachedUI.querySelector(".uploader-attached-remove");

    // default state
    progressUI.style.display = 'none';
    attachedUI.style.display = 'none';

    function prettySize(n) {
        if(n<1000) {
            return '' + n + 'B';
        }
        if(n<100000) {
            return '' + (n/1000).toPrecision(2) + 'KB';
        }
        if(n<100000) {
            return '' + (n/1000).toPrecision(3) + 'KB';
        }
        if(n<1000000) {
            return '' + (n/1000).toPrecision(4) + 'KB';
        }
        return '' + (n/1000000).toPrecision(2) + 'MB';
    }

    // TODO: add/remove fld-error classes
    function setError(errMsg) {
        errorUI.style.display = '';
        errorUI.textContent = errMsg;
    }

    function clearError(errMsg) {
        errorUI.style.display = 'none';
        errorUI.textContent = '';
    }

    // wire up remove button
    remove.addEventListener('click', function(e) {
        // remove a file that's been uploaded
        e.preventDefault();
        targ.style.display = '';
        targ.disabled = false;
        progressUI.style.display = 'none';
        clearError();
        attachedUI.style.display = 'none';
    });


    if( targ.getAttribute('data-uploaded')) {
        // show uploaded file,  with remove button
        targ.style.display = 'none';
        clearError();
        progressUI.style.display = 'none';
        attachedUI.style.display = '';
        details.textContent = targ.getAttribute('data-uploaded');
        // remove it from form submission
        targ.required = false;
        targ.autocomplete = 'off';  // to stop FF persisting disabled state across loads
        targ.disabled = true;
    }


    // when user picks a file...
    targ.addEventListener('change', function (evt) {
        var xhr = new XMLHttpRequest();

        var file = evt.target.files[0];
        // Verify file details
        // TODO


        function cancelClicked(e) {
            e.preventDefault();
            xhr.abort();
        }



        // set up the upload
        xhr.upload.addEventListener('progress', function(e) {

            if( e.lengthComputable ) {
                var done = e.loaded, total = e.total;
                progress.setAttribute("value",e.loaded);
                progress.setAttribute("max",e.total);
                progressMsg.innerHTML = ('' + Math.floor(e.loaded/e.total*100) + '% of ' + prettySize(e.total) + '<br/>' + file.name );
            } else {
                //TODO
            }
        }, false);
        xhr.addEventListener("load", function(e) {
            if(xhr.status != 200 ) {
                progressUI.style.display = 'none';
                attachedUI.style.display = 'none';
                setError('upload failed (http status ' + xhr.status + ')');
                targ.style.display = '';
                targ.disabled = false;
                return;
            }
            // success!
            clearError();
            progressUI.style.display = 'none';
            attachedUI.style.display = '';
            details.textContent = '' + file.name + ' (' + prettySize(file.size) + ')';
            // remove it from form submission
            targ.required = false;
            targ.autocomplete = 'off';  // to stop FF persisting disabled state across loads
            targ.disabled = true;
        }, false);
        xhr.addEventListener("error", function(e) {
            progressUI.style.display = 'none';
            attachedUI.style.display = 'none';
            setError('upload failed');
            targ.style.display = '';
            targ.disabled = false;
        }, false);
        xhr.addEventListener("abort", function(e) {
            clearError();
            targ.style.display = '';
            progressUI.style.display = 'none';
            attachedUI.style.display = 'none';
        }, false);

        xhr.open('post',document.URL);

        var token = document.getElementById('async_upload_token').value;
        var fd = new FormData;
        fd.append('async_upload_field', targ.name);
        fd.append('async_upload_file', file );
        fd.append('async_upload_token', token);


        // configure ui
        clearError();
        targ.style.display = 'none';
        progressUI.style.display = '';
        attachedUI.style.display = 'none';

        progress.setAttribute('value',0);
        //progressMsg.textContent = "Uploading " + file.name;
        cancel.addEventListener('click', cancelClicked);

        // go!
        xhr.send(fd);

    }, false);


}

