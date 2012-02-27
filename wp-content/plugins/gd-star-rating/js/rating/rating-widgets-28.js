function gdsrShowHidePreview(gdid) {
    var preview = document.getElementById(gdid+'-on');
    var message = document.getElementById(gdid+'-off');
    var hidden = document.getElementById(gdid);

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

function gdsrChangeSource(gdid, el) {
    document.getElementById(gdid + '-multis').style.display = el == "multis" ? "block" : "none";
}

function gdsrChangeTaxonomy(gdid, el) {
    document.getElementById(gdid + "-tax").style.display = el == "taxonomy" ? "block" : "none";
}

function gdsrChangeDate(gdid, el) {
    document.getElementById(gdid + "-lastd").style.display = el == "lastd" ? "block" : "none";
    document.getElementById(gdid + "-month").style.display = el == "month" ? "block" : "none";
    document.getElementById(gdid + "-range").style.display = el == "range" ? "block" : "none";
}

function gdsrChangeTrend(trend, el, gdid) {
    document.getElementById(gdid + "-" + trend + "-txt").style.display = el == "txt" ? "block" : "none";
    document.getElementById(gdid + "-" + trend + "-img").style.display = el == "img" ? "block" : "none";
}

function gdsrChangeImage(gdid, el) {
    document.getElementById(gdid + "-none").style.display = el == "none" ? "block" : "none";
    document.getElementById(gdid + "-custom").style.display = el == "custom" ? "block" : "none";
    document.getElementById(gdid + "-content").style.display = el == "content" ? "block" : "none";
}
