/*

 MyFonts Webfont Build ID 3066892, 2015-07-28T15:13:43-0400

 The fonts listed in this notice are subject to the End User License
 Agreement(s) entered into by the website owner. All other parties are 
 explicitly restricted from using the Licensed Webfonts(s).

 You may obtain a valid license at the URLs below.

 Webfont: MuseoSans-700 by exljbris
 URL: http://www.myfonts.com/fonts/exljbris/museo-sans/700/

 Webfont: MuseoSans-300 by exljbris
 URL: http://www.myfonts.com/fonts/exljbris/museo-sans/300/

 Webfont: MuseoSans-500 by exljbris
 URL: http://www.myfonts.com/fonts/exljbris/museo-sans/500/


 License: http://www.myfonts.com/viewlicense?type=web&buildid=3066892
 Licensed pageviews: 13,000,000
 Webfonts copyright: Copyright (c) 2008 by Jos Buivenga. All rights reserved.

 ? 2015 MyFonts Inc
*/
//tracking impression of font use
var protocol = document.location.protocol;
"https:" != protocol && (protocol = "http:");
var count = document.createElement("script");
count.type = "text/javascript";
count.async = !0;
count.src = protocol + "//hello.myfonts.net/count/2ecc0c";
//????tracking 
var s = document.getElementsByTagName("script")[0];
s.parentNode.insertBefore(count, s);
//
var browserName, browserVersion, webfontType;
if ("undefined" == typeof woffEnabled) var woffEnabled = !0;
var svgEnabled = 1,
    woff2Enabled = 0;
if ("undefined" != typeof customPath) var path = customPath;
else {
    var scripts = document.getElementsByTagName("SCRIPT"),
        script = scripts[scripts.length - 1].src;
    script.match("://") || "/" == script.charAt(0) || (script = "./" + script);
    path = script.replace(/\\/g, "/").replace(/\/[^\/]*\/?$/, "")
}
var wfpath = path + "/../fonts/",
    browsers = [{
        regex: "MSIE (\\d+\\.\\d+)",
        versionRegex: "new Number(RegExp.$1)",
        type: [{
            version: 9,
            type: "woff"
        }, {
            version: 5,
            type: "eot"
        }]
    }, {
        regex: "Trident/(\\d+\\.\\d+); (.+)?rv:(\\d+\\.\\d+)",
        versionRegex: "new Number(RegExp.$3)",
        type: [{
            version: 11,
            type: "woff"
        }]
    }, {
        regex: "Firefox[/s](\\d+\\.\\d+)",
        versionRegex: "new Number(RegExp.$1)",
        type: [{
            version: 3.6,
            type: "woff"
        }, {
            version: 3.5,
            type: "ttf"
        }]
    }, {
        regex: "Chrome/(\\d+\\.\\d+)",
        versionRegex: "new Number(RegExp.$1)",
        type: [{
            version: 36,
            type: "woff2"
        }, {
            version: 6,
            type: "woff"
        }, {
            version: 4,
            type: "ttf"
        }]
    }, {
        regex: "Mozilla.*Android (\\d+\\.\\d+).*AppleWebKit.*Safari",
        versionRegex: "new Number(RegExp.$1)",
        type: [{
            version: 4.1,
            type: "woff"
        }, {
            version: 3.1,
            type: "svg#wf"
        }, {
            version: 2.2,
            type: "ttf"
        }]
    }, {
        regex: "Mozilla.*(iPhone|iPad).* OS (\\d+)_(\\d+).* AppleWebKit.*Safari",
        versionRegex: "new Number(RegExp.$2) + (new Number(RegExp.$3) / 10)",
        unhinted: !0,
        type: [{
            version: 5,
            type: "woff"
        }, {
            version: 4.2,
            type: "ttf"
        }, {
            version: 1,
            type: "svg#wf"
        }]
    }, {
        regex: "Mozilla.*(iPhone|iPad|BlackBerry).*AppleWebKit.*Safari",
        versionRegex: "1.0",
        type: [{
            version: 1,
            type: "svg#wf"
        }]
    }, {
        regex: "Version/(\\d+\\.\\d+)(\\.\\d+)? Safari/(\\d+\\.\\d+)",
        versionRegex: "new Number(RegExp.$1)",
        type: [{
            version: 5.1,
            type: "woff"
        }, {
            version: 3.1,
            type: "ttf"
        }]
    }, {
        regex: "Opera/(\\d+\\.\\d+)(.+)Version/(\\d+\\.\\d+)(\\.\\d+)?",
        versionRegex: "new Number(RegExp.$3)",
        type: [{
            version: 24,
            type: "woff2"
        }, {
            version: 11.1,
            type: "woff"
        }, {
            version: 10.1,
            type: "ttf"
        }]
    }],
    browLen = browsers.length,
    suffix = "",
    i = 0;
a: for (; i < browLen; i++) {
    var regex = RegExp(browsers[i].regex);
    if (regex.test(navigator.userAgent)) {
        browserVersion = eval(browsers[i].versionRegex);
        var typeLen = browsers[i].type.length;
        for (j = 0; j < typeLen; j++) if (browserVersion >= browsers[i].type[j].version && (!0 == browsers[i].unhinted && (suffix = "_unhinted"), webfontType = browsers[i].type[j].type, "woff" != webfontType || woffEnabled) && ("woff2" != webfontType || woff2Enabled) && ("svg#wf" != webfontType || svgEnabled)) break a
    } else webfontType = "woff"
}
/(Macintosh|Android)/.test(navigator.userAgent) && "svg#wf" != webfontType && (suffix = "_unhinted");
var head = document.getElementsByTagName("head")[0],
	data_fn;
"ttf" == webfontType ? data_fn = "_unhinted" == suffix ? "2ECC0C_data_unhintedttf.css" : "2ECC0C_datattf.css" : "woff" == webfontType && (data_fn = "_unhinted" == suffix ? "2ECC0C_data_unhintedwoff.css" : "2ECC0C_datawoff.css");
var link = document.createElement("link");
link.setAttribute("rel", "stylesheet");
link.setAttribute("type", "text/css");
link.setAttribute("href", wfpath + data_fn);
head.appendChild(link);




