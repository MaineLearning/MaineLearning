function gdsrShowHidePreview(gdid, index) {
    var preview = document.getElementById(gdid+'-on['+index+']');
    var message = document.getElementById(gdid+'-off['+index+']');
    var hidden = document.getElementById(gdid+'['+index+']');

    if (preview.style.display == "block") {
        preview.style.display = "none";
        message.style.display = "block";
        hidden.value = "0";
    } else {
        preview.style.display = "block";
        message.style.display = "none";
        hidden.value = "1";
    }
}

function gdsrChangeSource(el, index) {
    document.getElementById("gdsr-src-multi["+index+"]").style.display = el == "multis" ? "block" : "none";
}

function gdsrChangeTaxonomy(el, index) {
    document.getElementById("gdsr-src-tax["+index+"]").style.display = el == "taxonomy" ? "block" : "none";
}

function gdsrChangeDate(el, index) {
    document.getElementById("gdsr-pd-lastd["+index+"]").style.display = el == "lastd" ? "block" : "none";
    document.getElementById("gdsr-pd-month["+index+"]").style.display = el == "month" ? "block" : "none";
    document.getElementById("gdsr-pd-range["+index+"]").style.display = el == "range" ? "block" : "none";
}

function gdsrChangeTrend(trend, el, index) {
    document.getElementById("gdsr-"+trend+"-txt["+index+"]").style.display = el == "txt" ? "block" : "none";
    document.getElementById("gdsr-"+trend+"-img["+index+"]").style.display = el == "img" ? "block" : "none";
}

function gdsrChangeImage(el, index) {
    document.getElementById("gdsr-img-none["+index+"]").style.display = el == "none" ? "block" : "none";
    document.getElementById("gdsr-img-custom["+index+"]").style.display = el == "custom" ? "block" : "none";
    document.getElementById("gdsr-img-content["+index+"]").style.display = el == "content" ? "block" : "none";
}
