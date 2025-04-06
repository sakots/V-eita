function l() {
    var P = loadCookie("pwd_c"),
        N = loadCookie("name_c"),
        E = loadCookie("email_c"),
        U = loadCookie("url_c"),
        FC = loadCookie("f_color_c"),
        AP = loadCookie("tool_c"),
        PW = loadCookie("pic_w_c"),
        PH = loadCookie("pic_h_c"),
        PL = loadCookie("palette_c"),
        i;
    for (i = 0; i < document.forms.length; i++) {
        if (document.forms[i].pwd) {
            document.forms[i].pwd.value = P;
        }
        if (document.forms[i].name) {
            document.forms[i].name.value = N;
        }
        if (document.forms[i].email) {
            document.forms[i].email.value = E;
        }
        if (document.forms[i].url) {
            document.forms[i].url.value = U;
        }
        if (document.forms[i].f_color) {
            if (FC == "") FC = document.forms[i].f_color[0].value;
            checked_if_formval_equal_cookieval(document.forms[i].f_color, FC);
        }
        if (document.forms[i].tools) {
            checked_if_formval_equal_cookieval(document.forms[i].tools, AP);
        }
        if (document.forms[i].pic_w) {
            if (PW != "") {
                document.forms[i].pic_w.value = PW;
            }
            checked_if_formval_equal_cookieval(document.forms[i].pic_w, PW);
        }
        if (document.forms[i].pic_h) {
            if (PH != "") {
                document.forms[i].pic_h.value = PH;
            }
            checked_if_formval_equal_cookieval(document.forms[i].pic_h, PH);
        }
        if (document.forms[i].palettes) {
            if (PL != "") {
                document.forms[i].palettes.selectedIndex = PL;
            }
            checked_if_formval_equal_cookieval(document.forms[i].palettes, PL);
        }
    }
};

//Cookieと一致したらchecked
function checked_if_formval_equal_cookieval(docformsname, cookieval) {
    var j;
    for (j = 0; docformsname.length > j; j++) {
        if (docformsname[j].value == cookieval) {
            docformsname[j].checked = true; //チェックボックス
            docformsname.selectedIndex = j; //プルダウンメニュー
        }
    }
}

/* Function to get cookie parameter value string with specified name
   Copyright (C) 2002 Cresc Corp. http://www.cresc.co.jp/
   Version: 1.0
*/
function loadCookie(name) {
    let allCookies = document.cookie;
    if (allCookies == "") return "";
    let start = allCookies.indexOf(name + "=");
    if (start == -1) return "";
    start += name.length + 1;
    let end = allCookies.indexOf(';', start);
    if (end == -1) end = allCookies.length;

    return decodeURIComponent(allCookies.substring(start, end));
}
