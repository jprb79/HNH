(function(V, q) {
    var g = {
        version: "3.0.3"
    };
    var aa = navigator.userAgent.toLowerCase();
    if (aa.indexOf("windows") > -1 || aa.indexOf("win32") > -1) {
        g.isWindows = true
    } else {
        if (aa.indexOf("macintosh") > -1 || aa.indexOf("mac os x") > -1) {
            g.isMac = true
        } else {
            if (aa.indexOf("linux") > -1) {
                g.isLinux = true
            }
        }
    }
    g.isIE = aa.indexOf("msie") > -1;
    g.isIE6 = aa.indexOf("msie 6") > -1;
    g.isIE7 = aa.indexOf("msie 7") > -1;
    g.isGecko = aa.indexOf("gecko") > -1 && aa.indexOf("safari") == -1;
    g.isWebKit = aa.indexOf("applewebkit/") > -1;
    var e = /#(.+)$/,
    O = /^(light|shadow)box\[(.*?)\]/i,
    o = /\s*([a-z_]*?)\s*=\s*(.+)\s*/,
    au = /[0-9a-z]+$/i,
    ar = /(.+\/)shadowbox\.js/i;
    var x = false,
    m = false,
    X = {},
    ak = 0,
    Q, ac;
    g.current = -1;
    g.dimensions = null;
    g.ease = function(E) {
        return 1 + Math.pow(E - 1, 3)
    };
    g.errorInfo = {
        fla: {
            name: "Flash",
            url: "http://www.adobe.com/products/flashplayer/"
        },
        qt: {
            name: "QuickTime",
            url: "http://www.apple.com/quicktime/download/"
        },
        wmp: {
            name: "Windows Media Player",
            url: "http://www.microsoft.com/windows/windowsmedia/"
        },
        f4m: {
            name: "Flip4Mac",
            url: "http://www.flip4mac.com/wmv_download.htm"
        }
    };
    g.gallery = [];
    g.onReady = al;
    g.path = null;
    g.player = null;
    g.playerId = "sb-player";
    g.options = {
        animate: true,
        animateFade: true,
        autoplayMovies: true,
        continuous: false,
        enableKeys: true,
        flashParams: {
            bgcolor: "#000000",
            allowfullscreen: true
        },
        flashVars: {},
        flashVersion: "9.0.115",
        handleOversize: "resize",
        handleUnsupported: "link",
        onChange: al,
        onClose: al,
        onFinish: al,
        onOpen: al,
        showMovieControls: true,
        skipSetup: false,
        slideshowDelay: 0,
        viewportPadding: 20
    };
    g.getCurrent = function() {
        return g.current > -1 ? g.gallery[g.current] : null
    };
    g.hasNext = function() {
        return g.gallery.length > 1 && (g.current != g.gallery.length - 1 || g.options.continuous)
    };
    g.isOpen = function() {
        return x
    };
    g.isPaused = function() {
        return ac == "pause"
    };
    g.applyOptions = function(E) {
        X = aq({},
        g.options);
        aq(g.options, E)
    };
    g.revertOptions = function() {
        aq(g.options, X)
    };
    g.init = function(S, ay) {
        if (m) {
            return
        }
        m = true;
        if (g.skin.options) {
            aq(g.options, g.skin.options)
        }
        if (S) {
            aq(g.options, S)
        }
        if (!g.path) {
            var ax, K = document.getElementsByTagName("script");
            for (var aw = 0,
            E = K.length; aw < E; ++aw) {
                ax = ar.exec(K[aw].src);
                if (ax) {
                    g.path = ax[1];
                    break
                }
            }
        }
        if (ay) {
            g.onReady = ay
        }
        at()
    };
    g.open = function(K) {
        if (x) {
            return
        }
        var E = g.makeGallery(K);
        g.gallery = E[0];
        g.current = E[1];
        K = g.getCurrent();
        if (K == null) {
            return
        }
        g.applyOptions(K.options || {});
        f();
        if (g.gallery.length) {
            K = g.getCurrent();
            if (g.options.onOpen(K) === false) {
                return
            }
            x = true;
            g.skin.onOpen(K, W)
        }
    };
    g.close = function() {
        if (!x) {
            return
        }
        x = false;
        if (g.player) {
            g.player.remove();
            g.player = null
        }
        if (typeof ac == "number") {
            clearTimeout(ac);
            ac = null
        }
        ak = 0;
        ah(false);
        g.options.onClose(g.getCurrent());
        g.skin.onClose();
        g.revertOptions()
    };
    g.play = function() {
        if (!g.hasNext()) {
            return
        }
        if (!ak) {
            ak = g.options.slideshowDelay * 1000
        }
        if (ak) {
            Q = Z();
            ac = setTimeout(function() {
                ak = Q = 0;
                g.next()
            },
            ak);
            if (g.skin.onPlay) {
                g.skin.onPlay()
            }
        }
    };
    g.pause = function() {
        if (typeof ac != "number") {
            return
        }
        ak = Math.max(0, ak - (Z() - Q));
        if (ak) {
            clearTimeout(ac);
            ac = "pause";
            if (g.skin.onPause) {
                g.skin.onPause()
            }
        }
    };
    g.change = function(E) {
        if (! (E in g.gallery)) {
            if (g.options.continuous) {
                E = (E < 0 ? g.gallery.length + E: 0);
                if (! (E in g.gallery)) {
                    return
                }
            } else {
                return
            }
        }
        g.current = E;
        if (typeof ac == "number") {
            clearTimeout(ac);
            ac = null;
            ak = Q = 0
        }
        g.options.onChange(g.getCurrent());
        W(true)
    };
    g.next = function() {
        g.change(g.current + 1)
    };
    g.previous = function() {
        g.change(g.current - 1)
    };
    g.setDimensions = function(aH, ay, aF, aG, ax, E, aD, aA) {
        var aC = aH,
        aw = ay;
        var aB = 2 * aD + ax;
        if (aH + aB > aF) {
            aH = aF - aB
        }
        var S = 2 * aD + E;
        if (ay + S > aG) {
            ay = aG - S
        }
        var K = (aC - aH) / aC,
        aE = (aw - ay) / aw,
        az = (K > 0 || aE > 0);
        if (aA && az) {
            if (K > aE) {
                ay = Math.round((aw / aC) * aH)
            } else {
                if (aE > K) {
                    aH = Math.round((aC / aw) * ay)
                }
            }
        }
        g.dimensions = {
            height: aH + ax,
            width: ay + E,
            innerHeight: aH,
            innerWidth: ay,
            top: Math.floor((aF - (aH + aB)) / 2 + aD),
            left: Math.floor((aG - (ay + S)) / 2 + aD),
            oversized: az
        };
        return g.dimensions
    };
    g.makeGallery = function(ax) {
        var E = [],
        aw = -1;
        if (typeof ax == "string") {
            ax = [ax]
        }
        if (typeof ax.length == "number") {
            ae(ax,
            function(az, aA) {
                if (aA.content) {
                    E[az] = aA
                } else {
                    E[az] = {
                        content: aA
                    }
                }
            });
            aw = 0
        } else {
            if (ax.tagName) {
                var K = g.getCache(ax);
                ax = K ? K: g.makeObject(ax)
            }
            if (ax.gallery) {
                E = [];
                var ay;
                for (var S in g.cache) {
                    ay = g.cache[S];
                    if (ay.gallery && ay.gallery == ax.gallery) {
                        if (aw == -1 && ay.content == ax.content) {
                            aw = E.length
                        }
                        E.push(ay)
                    }
                }
                if (aw == -1) {
                    E.unshift(ax);
                    aw = 0
                }
            } else {
                E = [ax];
                aw = 0
            }
        }
        ae(E,
        function(az, aA) {
            E[az] = aq({},
            aA)
        });
        return [E, aw]
    };
    g.makeObject = function(aw, S) {
        var ax = {
            content: aw.href,
            title: aw.getAttribute("title") || "",
            link: aw
        };
        if (S) {
            S = aq({},
            S);
            ae(["player", "title", "height", "width", "gallery"],
            function(ay, az) {
                if (typeof S[az] != "undefined") {
                    ax[az] = S[az];
                    delete S[az]
                }
            });
            ax.options = S
        } else {
            ax.options = {}
        }
        if (!ax.player) {
            ax.player = g.getPlayer(ax.content)
        }
        var E = aw.getAttribute("rel");
        if (E) {
            var K = E.match(O);
            if (K) {
                ax.gallery = escape(K[2])
            }
            ae(E.split(";"),
            function(ay, az) {
                K = az.match(o);
                if (K) {
                    ax[K[1]] = K[2]
                }
            })
        }
        return ax
    };
    g.getPlayer = function(S) {
        if (S.indexOf("#") > -1 && S.indexOf(document.location.href) == 0) {
            return "inline"
        }
        var aw = S.indexOf("?");
        if (aw > -1) {
            S = S.substring(0, aw)
        }
        var K, E = S.match(au);
        if (E) {
            K = E[0].toLowerCase()
        }
        if (K) {
            if (g.img && g.img.ext.indexOf(K) > -1) {
                return "img"
            }
            if (g.swf && g.swf.ext.indexOf(K) > -1) {
                return "swf"
            }
            if (g.flv && g.flv.ext.indexOf(K) > -1) {
                return "flv"
            }
            if (g.qt && g.qt.ext.indexOf(K) > -1) {
                if (g.wmp && g.wmp.ext.indexOf(K) > -1) {
                    return "qtwmp"
                } else {
                    return "qt"
                }
            }
            if (g.wmp && g.wmp.ext.indexOf(K) > -1) {
                return "wmp"
            }
        }
        return "iframe"
    };
    function f() {
        var aw = g.errorInfo,
        ax = g.plugins,
        az, aA, aD, S, aC, K, aB, E;
        for (var ay = 0; ay < g.gallery.length; ++ay) {
            az = g.gallery[ay];
            aA = false;
            aD = null;
            switch (az.player) {
            case "flv":
            case "swf":
                if (!ax.fla) {
                    aD = "fla"
                }
                break;
            case "qt":
                if (!ax.qt) {
                    aD = "qt"
                }
                break;
            case "wmp":
                if (g.isMac) {
                    if (ax.qt && ax.f4m) {
                        az.player = "qt"
                    } else {
                        aD = "qtf4m"
                    }
                } else {
                    if (!ax.wmp) {
                        aD = "wmp"
                    }
                }
                break;
            case "qtwmp":
                if (ax.qt) {
                    az.player = "qt"
                } else {
                    if (ax.wmp) {
                        az.player = "wmp"
                    } else {
                        aD = "qtwmp"
                    }
                }
                break
            }
            if (aD) {
                if (g.options.handleUnsupported == "link") {
                    switch (aD) {
                    case "qtf4m":
                        aC = "shared";
                        K = [aw.qt.url, aw.qt.name, aw.f4m.url, aw.f4m.name];
                        break;
                    case "qtwmp":
                        aC = "either";
                        K = [aw.qt.url, aw.qt.name, aw.wmp.url, aw.wmp.name];
                        break;
                    default:
                        aC = "single";
                        K = [aw[aD].url, aw[aD].name]
                    }
                    az.player = "html";
                    az.content = '<div class="sb-message">' + t(g.lang.errors[aC], K) + "</div>"
                } else {
                    aA = true
                }
            } else {
                if (az.player == "inline") {
                    S = e.exec(az.content);
                    if (S) {
                        aB = ai(S[1]);
                        if (aB) {
                            az.content = aB.innerHTML
                        } else {
                            aA = true
                        }
                    } else {
                        aA = true
                    }
                } else {
                    if (az.player == "swf" || az.player == "flv") {
                        E = (az.options && az.options.flashVersion) || g.options.flashVersion;
                        if (g.flash && !g.flash.hasFlashPlayerVersion(E)) {
                            az.width = 310;
                            az.height = 177
                        }
                    }
                }
            }
            if (aA) {
                g.gallery.splice(ay, 1);
                if (ay < g.current) {--g.current
                } else {
                    if (ay == g.current) {
                        g.current = ay > 0 ? ay - 1 : ay
                    }
                }--ay
            }
        }
    }
    function ah(E) {
        if (!g.options.enableKeys) {
            return
        } (E ? j: a)(document, "keydown", Y)
    }
    function Y(S) {
        if (S.metaKey || S.shiftKey || S.altKey || S.ctrlKey) {
            return
        }
        var K = l(S),
        E;
        switch (K) {
        case 81:
        case 88:
        case 27:
            E = g.close;
            break;
        case 37:
            E = g.previous;
            break;
        case 39:
            E = g.next;
            break;
        case 32:
            E = typeof ac == "number" ? g.pause: g.play;
            break
        }
        if (E) {
            I(S);
            E()
        }
    }
    function W(az) {
        ah(false);
        var ay = g.getCurrent();
        var S = (ay.player == "inline" ? "html": ay.player);
        if (typeof g[S] != "function") {
            throw "unknown player " + S
        }
        if (az) {
            g.player.remove();
            g.revertOptions();
            g.applyOptions(ay.options || {})
        }
        g.player = new g[S](ay, g.playerId);
        if (g.gallery.length > 1) {
            var aw = g.gallery[g.current + 1] || g.gallery[0];
            if (aw.player == "img") {
                var K = new Image();
                K.src = aw.content
            }
            var ax = g.gallery[g.current - 1] || g.gallery[g.gallery.length - 1];
            if (ax.player == "img") {
                var E = new Image();
                E.src = ax.content
            }
        }
        g.skin.onLoad(az, s)
    }
    function s() {
        if (!x) {
            return
        }
        if (typeof g.player.ready != "undefined") {
            var E = setInterval(function() {
                if (x) {
                    if (g.player.ready) {
                        clearInterval(E);
                        E = null;
                        g.skin.onReady(M)
                    }
                } else {
                    clearInterval(E);
                    E = null
                }
            },
            10)
        } else {
            g.skin.onReady(M)
        }
    }
    function M() {
        if (!x) {
            return
        }
        g.player.append(g.skin.body, g.dimensions);
        g.skin.onShow(r)
    }
    function r() {
        if (!x) {
            return
        }
        if (g.player.onLoad) {
            g.player.onLoad()
        }
        g.options.onFinish(g.getCurrent());
        if (!g.isPaused()) {
            g.play()
        }
        ah(true)
    }
    if (!Array.prototype.indexOf) {
        Array.prototype.indexOf = function(K, S) {
            var E = this.length >>> 0;
            S = S || 0;
            if (S < 0) {
                S += E
            }
            for (; S < E; ++S) {
                if (S in this && this[S] === K) {
                    return S
                }
            }
            return - 1
        }
    }
    function Z() {
        return (new Date).getTime()
    }
    function aq(E, S) {
        for (var K in S) {
            E[K] = S[K]
        }
        return E
    }
    function ae(aw, ax) {
        var K = 0,
        E = aw.length;
        for (var S = aw[0]; K < E && ax.call(S, K, S) !== false; S = aw[++K]) {}
    }
    function t(K, E) {
        return K.replace(/\{(\w+?)\}/g,
        function(S, aw) {
            return E[aw]
        })
    }
    function al() {}
    function ai(E) {
        return document.getElementById(E)
    }
    function A(E) {
        E.parentNode.removeChild(E)
    }
    var am = true,
    N = true;
    function ap() {
        var E = document.body,
        K = document.createElement("div");
        am = typeof K.style.opacity === "string";
        K.style.position = "fixed";
        K.style.margin = 0;
        K.style.top = "20px";
        E.appendChild(K, E.firstChild);
        N = K.offsetTop == 20;
        E.removeChild(K)
    }
    g.getStyle = (function() {
        var E = /opacity=([^)]*)/,
        K = document.defaultView && document.defaultView.getComputedStyle;
        return function(ay, ax) {
            var aw;
            if (!am && ax == "opacity" && ay.currentStyle) {
                aw = E.test(ay.currentStyle.filter || "") ? (parseFloat(RegExp.$1) / 100) + "": "";
                return aw === "" ? "1": aw
            }
            if (K) {
                var S = K(ay, null);
                if (S) {
                    aw = S[ax]
                }
                if (ax == "opacity" && aw == "") {
                    aw = "1"
                }
            } else {
                aw = ay.currentStyle[ax]
            }
            return aw
        }
    })();
    g.appendHTML = function(S, K) {
        if (S.insertAdjacentHTML) {
            S.insertAdjacentHTML("BeforeEnd", K)
        } else {
            if (S.lastChild) {
                var E = S.ownerDocument.createRange();
                E.setStartAfter(S.lastChild);
                var aw = E.createContextualFragment(K);
                S.appendChild(aw)
            } else {
                S.innerHTML = K
            }
        }
    };
    g.getWindowSize = function(E) {
        if (document.compatMode === "CSS1Compat") {
            return document.documentElement["client" + E]
        }
        return document.body["client" + E]
    };
    g.setOpacity = function(S, E) {
        var K = S.style;
        if (am) {
            K.opacity = (E == 1 ? "": E)
        } else {
            K.zoom = 1;
            if (E == 1) {
                if (typeof K.filter == "string" && (/alpha/i).test(K.filter)) {
                    K.filter = K.filter.replace(/\s*[\w\.]*alpha\([^\)]*\);?/gi, "")
                }
            } else {
                K.filter = (K.filter || "").replace(/\s*[\w\.]*alpha\([^\)]*\)/gi, "") + " alpha(opacity=" + (E * 100) + ")"
            }
        }
    };
    g.clearOpacity = function(E) {
        g.setOpacity(E, 1)
    };
    var p = Event;
    function D(E) {
        return p.element(E)
    }
    function T(E) {
        return [p.pointerX(E), p.pointerY(E)]
    }
    function I(E) {
        p.stop(E)
    }
    function l(E) {
        return E.keyCode
    }
    function j(S, K, E) {
        p.observe(S, K, E)
    }
    function a(S, K, E) {
        p.stopObserving(S, K, E)
    }
    var F = false,
    P;
    if (document.addEventListener) {
        P = function() {
            document.removeEventListener("DOMContentLoaded", P, false);
            g.load()
        }
    } else {
        if (document.attachEvent) {
            P = function() {
                if (document.readyState === "complete") {
                    document.detachEvent("onreadystatechange", P);
                    g.load()
                }
            }
        }
    }
    function i() {
        if (F) {
            return
        }
        try {
            document.documentElement.doScroll("left")
        } catch(E) {
            setTimeout(i, 1);
            return
        }
        g.load()
    }
    function at() {
        if (document.readyState === "complete") {
            return g.load()
        }
        if (document.addEventListener) {
            document.addEventListener("DOMContentLoaded", P, false);
            V.addEventListener("load", g.load, false)
        } else {
            if (document.attachEvent) {
                document.attachEvent("onreadystatechange", P);
                V.attachEvent("onload", g.load);
                var E = false;
                try {
                    E = V.frameElement === null
                } catch(K) {}
                if (document.documentElement.doScroll && E) {
                    i()
                }
            }
        }
    }
    g.load = function() {
        if (F) {
            return
        }
        if (!document.body) {
            return setTimeout(g.load, 13)
        }
        F = true;
        ap();
        g.onReady();
        if (!g.options.skipSetup) {
            g.setup()
        }
        g.skin.init()
    };
    g.plugins = {};
    if (navigator.plugins && navigator.plugins.length) {
        var ao = [];
        ae(navigator.plugins,
        function(E, K) {
            ao.push(K.name)
        });
        ao = ao.join(",");
        var d = ao.indexOf("Flip4Mac") > -1;
        g.plugins = {
            fla: ao.indexOf("Shockwave Flash") > -1,
            qt: ao.indexOf("QuickTime") > -1,
            wmp: !d && ao.indexOf("Windows Media") > -1,
            f4m: d
        }
    } else {
        var C = function(E) {
            var K;
            try {
                K = new ActiveXObject(E)
            } catch(S) {}
            return !! K
        };
        g.plugins = {
            fla: C("ShockwaveFlash.ShockwaveFlash"),
            qt: C("QuickTime.QuickTime"),
            wmp: C("wmplayer.ocx"),
            f4m: false
        }
    }
    var c = /^(light|shadow)box/i,
    ab = "shadowboxCacheKey",
    h = 1;
    g.cache = {};
    g.select = function(K) {
        var S = [];
        if (!K) {
            var E;
            ae(document.getElementsByTagName("a"),
            function(ay, az) {
                E = az.getAttribute("rel");
                if (E && c.test(E)) {
                    S.push(az)
                }
            })
        } else {
            var ax = K.length;
            if (ax) {
                if (typeof K == "string") {
                    if (g.find) {
                        S = g.find(K)
                    }
                } else {
                    if (ax == 2 && typeof K[0] == "string" && K[1].nodeType) {
                        if (g.find) {
                            S = g.find(K[0], K[1])
                        }
                    } else {
                        for (var aw = 0; aw < ax; ++aw) {
                            S[aw] = K[aw]
                        }
                    }
                }
            } else {
                S.push(K)
            }
        }
        return S
    };
    g.setup = function(E, K) {
        ae(g.select(E),
        function(S, aw) {
            g.addCache(aw, K)
        })
    };
    g.teardown = function(E) {
        ae(g.select(E),
        function(K, S) {
            g.removeCache(S)
        })
    };
    g.addCache = function(S, E) {
        var K = S[ab];
        if (K == q) {
            K = h++;
            S[ab] = K;
            j(S, "click", b)
        }
        g.cache[K] = g.makeObject(S, E)
    };
    g.removeCache = function(E) {
        a(E, "click", b);
        delete g.cache[E[ab]];
        E[ab] = null
    };
    g.getCache = function(K) {
        var E = K[ab];
        return (E in g.cache && g.cache[E])
    };
    g.clearCache = function() {
        for (var E in g.cache) {
            g.removeCache(g.cache[E].link)
        }
        g.cache = {}
    };
    function b(E) {
        g.open(this);
        if (g.gallery.length) {
            I(E)
        }
    }
    /*
 * Sizzle CSS Selector Engine - v1.0
 *  Copyright 2009, The Dojo Foundation
 *  Released under the MIT, BSD, and GPL Licenses.
 *  More information: http://sizzlejs.com/
 *
 * Modified for inclusion in Shadowbox.js
 */
    g.find = (function() {
        var aE = /((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^[\]]*\]|['"][^'"]*['"]|[^[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g,
        aF = 0,
        aH = Object.prototype.toString,
        az = false,
        ay = true;
        [0, 0].sort(function() {
            ay = false;
            return 0
        });
        var S = function(aQ, aL, aT, aU) {
            aT = aT || [];
            var aW = aL = aL || document;
            if (aL.nodeType !== 1 && aL.nodeType !== 9) {
                return []
            }
            if (!aQ || typeof aQ !== "string") {
                return aT
            }
            var aR = [],
            aN,
            aY,
            a1,
            aM,
            aP = true,
            aO = aw(aL),
            aV = aQ;
            while ((aE.exec(""), aN = aE.exec(aV)) !== null) {
                aV = aN[3];
                aR.push(aN[1]);
                if (aN[2]) {
                    aM = aN[3];
                    break
                }
            }
            if (aR.length > 1 && aA.exec(aQ)) {
                if (aR.length === 2 && aB.relative[aR[0]]) {
                    aY = aI(aR[0] + aR[1], aL)
                } else {
                    aY = aB.relative[aR[0]] ? [aL] : S(aR.shift(), aL);
                    while (aR.length) {
                        aQ = aR.shift();
                        if (aB.relative[aQ]) {
                            aQ += aR.shift()
                        }
                        aY = aI(aQ, aY)
                    }
                }
            } else {
                if (!aU && aR.length > 1 && aL.nodeType === 9 && !aO && aB.match.ID.test(aR[0]) && !aB.match.ID.test(aR[aR.length - 1])) {
                    var aX = S.find(aR.shift(), aL, aO);
                    aL = aX.expr ? S.filter(aX.expr, aX.set)[0] : aX.set[0]
                }
                if (aL) {
                    var aX = aU ? {
                        expr: aR.pop(),
                        set: aD(aU)
                    }: S.find(aR.pop(), aR.length === 1 && (aR[0] === "~" || aR[0] === "+") && aL.parentNode ? aL.parentNode: aL, aO);
                    aY = aX.expr ? S.filter(aX.expr, aX.set) : aX.set;
                    if (aR.length > 0) {
                        a1 = aD(aY)
                    } else {
                        aP = false
                    }
                    while (aR.length) {
                        var a0 = aR.pop(),
                        aZ = a0;
                        if (!aB.relative[a0]) {
                            a0 = ""
                        } else {
                            aZ = aR.pop()
                        }
                        if (aZ == null) {
                            aZ = aL
                        }
                        aB.relative[a0](a1, aZ, aO)
                    }
                } else {
                    a1 = aR = []
                }
            }
            if (!a1) {
                a1 = aY
            }
            if (!a1) {
                throw "Syntax error, unrecognized expression: " + (a0 || aQ)
            }
            if (aH.call(a1) === "[object Array]") {
                if (!aP) {
                    aT.push.apply(aT, a1)
                } else {
                    if (aL && aL.nodeType === 1) {
                        for (var aS = 0; a1[aS] != null; aS++) {
                            if (a1[aS] && (a1[aS] === true || a1[aS].nodeType === 1 && aC(aL, a1[aS]))) {
                                aT.push(aY[aS])
                            }
                        }
                    } else {
                        for (var aS = 0; a1[aS] != null; aS++) {
                            if (a1[aS] && a1[aS].nodeType === 1) {
                                aT.push(aY[aS])
                            }
                        }
                    }
                }
            } else {
                aD(a1, aT)
            }
            if (aM) {
                S(aM, aW, aT, aU);
                S.uniqueSort(aT)
            }
            return aT
        };
        S.uniqueSort = function(aM) {
            if (aG) {
                az = ay;
                aM.sort(aG);
                if (az) {
                    for (var aL = 1; aL < aM.length; aL++) {
                        if (aM[aL] === aM[aL - 1]) {
                            aM.splice(aL--, 1)
                        }
                    }
                }
            }
            return aM
        };
        S.matches = function(aL, aM) {
            return S(aL, null, null, aM)
        };
        S.find = function(aS, aL, aT) {
            var aR, aP;
            if (!aS) {
                return []
            }
            for (var aO = 0,
            aN = aB.order.length; aO < aN; aO++) {
                var aQ = aB.order[aO],
                aP;
                if ((aP = aB.leftMatch[aQ].exec(aS))) {
                    var aM = aP[1];
                    aP.splice(1, 1);
                    if (aM.substr(aM.length - 1) !== "\\") {
                        aP[1] = (aP[1] || "").replace(/\\/g, "");
                        aR = aB.find[aQ](aP, aL, aT);
                        if (aR != null) {
                            aS = aS.replace(aB.match[aQ], "");
                            break
                        }
                    }
                }
            }
            if (!aR) {
                aR = aL.getElementsByTagName("*")
            }
            return {
                set: aR,
                expr: aS
            }
        };
        S.filter = function(aV, aU, aY, aO) {
            var aN = aV,
            a0 = [],
            aS = aU,
            aQ,
            aL,
            aR = aU && aU[0] && aw(aU[0]);
            while (aV && aU.length) {
                for (var aT in aB.filter) {
                    if ((aQ = aB.match[aT].exec(aV)) != null) {
                        var aM = aB.filter[aT],
                        aZ,
                        aX;
                        aL = false;
                        if (aS === a0) {
                            a0 = []
                        }
                        if (aB.preFilter[aT]) {
                            aQ = aB.preFilter[aT](aQ, aS, aY, a0, aO, aR);
                            if (!aQ) {
                                aL = aZ = true
                            } else {
                                if (aQ === true) {
                                    continue
                                }
                            }
                        }
                        if (aQ) {
                            for (var aP = 0;
                            (aX = aS[aP]) != null; aP++) {
                                if (aX) {
                                    aZ = aM(aX, aQ, aP, aS);
                                    var aW = aO ^ !!aZ;
                                    if (aY && aZ != null) {
                                        if (aW) {
                                            aL = true
                                        } else {
                                            aS[aP] = false
                                        }
                                    } else {
                                        if (aW) {
                                            a0.push(aX);
                                            aL = true
                                        }
                                    }
                                }
                            }
                        }
                        if (aZ !== q) {
                            if (!aY) {
                                aS = a0
                            }
                            aV = aV.replace(aB.match[aT], "");
                            if (!aL) {
                                return []
                            }
                            break
                        }
                    }
                }
                if (aV === aN) {
                    if (aL == null) {
                        throw "Syntax error, unrecognized expression: " + aV
                    } else {
                        break
                    }
                }
                aN = aV
            }
            return aS
        };
        var aB = S.selectors = {
            order: ["ID", "NAME", "TAG"],
            match: {
                ID: /#((?:[\w\u00c0-\uFFFF-]|\\.)+)/,
                CLASS: /\.((?:[\w\u00c0-\uFFFF-]|\\.)+)/,
                NAME: /\[name=['"]*((?:[\w\u00c0-\uFFFF-]|\\.)+)['"]*\]/,
                ATTR: /\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/,
                TAG: /^((?:[\w\u00c0-\uFFFF\*-]|\\.)+)/,
                CHILD: /:(only|nth|last|first)-child(?:\((even|odd|[\dn+-]*)\))?/,
                POS: /:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^-]|$)/,
                PSEUDO: /:((?:[\w\u00c0-\uFFFF-]|\\.)+)(?:\((['"]*)((?:\([^\)]+\)|[^\2\(\)]*)+)\2\))?/
            },
            leftMatch: {},
            attrMap: {
                "class": "className",
                "for": "htmlFor"
            },
            attrHandle: {
                href: function(aL) {
                    return aL.getAttribute("href")
                }
            },
            relative: {
                "+": function(aR, aM) {
                    var aO = typeof aM === "string",
                    aQ = aO && !/\W/.test(aM),
                    aS = aO && !aQ;
                    if (aQ) {
                        aM = aM.toLowerCase()
                    }
                    for (var aN = 0,
                    aL = aR.length,
                    aP; aN < aL; aN++) {
                        if ((aP = aR[aN])) {
                            while ((aP = aP.previousSibling) && aP.nodeType !== 1) {}
                            aR[aN] = aS || aP && aP.nodeName.toLowerCase() === aM ? aP || false: aP === aM
                        }
                    }
                    if (aS) {
                        S.filter(aM, aR, true)
                    }
                },
                ">": function(aR, aM) {
                    var aP = typeof aM === "string";
                    if (aP && !/\W/.test(aM)) {
                        aM = aM.toLowerCase();
                        for (var aN = 0,
                        aL = aR.length; aN < aL; aN++) {
                            var aQ = aR[aN];
                            if (aQ) {
                                var aO = aQ.parentNode;
                                aR[aN] = aO.nodeName.toLowerCase() === aM ? aO: false
                            }
                        }
                    } else {
                        for (var aN = 0,
                        aL = aR.length; aN < aL; aN++) {
                            var aQ = aR[aN];
                            if (aQ) {
                                aR[aN] = aP ? aQ.parentNode: aQ.parentNode === aM
                            }
                        }
                        if (aP) {
                            S.filter(aM, aR, true)
                        }
                    }
                },
                "": function(aO, aM, aQ) {
                    var aN = aF++,
                    aL = aJ;
                    if (typeof aM === "string" && !/\W/.test(aM)) {
                        var aP = aM = aM.toLowerCase();
                        aL = E
                    }
                    aL("parentNode", aM, aN, aO, aP, aQ)
                },
                "~": function(aO, aM, aQ) {
                    var aN = aF++,
                    aL = aJ;
                    if (typeof aM === "string" && !/\W/.test(aM)) {
                        var aP = aM = aM.toLowerCase();
                        aL = E
                    }
                    aL("previousSibling", aM, aN, aO, aP, aQ)
                }
            },
            find: {
                ID: function(aM, aN, aO) {
                    if (typeof aN.getElementById !== "undefined" && !aO) {
                        var aL = aN.getElementById(aM[1]);
                        return aL ? [aL] : []
                    }
                },
                NAME: function(aN, aQ) {
                    if (typeof aQ.getElementsByName !== "undefined") {
                        var aM = [],
                        aP = aQ.getElementsByName(aN[1]);
                        for (var aO = 0,
                        aL = aP.length; aO < aL; aO++) {
                            if (aP[aO].getAttribute("name") === aN[1]) {
                                aM.push(aP[aO])
                            }
                        }
                        return aM.length === 0 ? null: aM
                    }
                },
                TAG: function(aL, aM) {
                    return aM.getElementsByTagName(aL[1])
                }
            },
            preFilter: {
                CLASS: function(aO, aM, aN, aL, aR, aS) {
                    aO = " " + aO[1].replace(/\\/g, "") + " ";
                    if (aS) {
                        return aO
                    }
                    for (var aP = 0,
                    aQ;
                    (aQ = aM[aP]) != null; aP++) {
                        if (aQ) {
                            if (aR ^ (aQ.className && (" " + aQ.className + " ").replace(/[\t\n]/g, " ").indexOf(aO) >= 0)) {
                                if (!aN) {
                                    aL.push(aQ)
                                }
                            } else {
                                if (aN) {
                                    aM[aP] = false
                                }
                            }
                        }
                    }
                    return false
                },
                ID: function(aL) {
                    return aL[1].replace(/\\/g, "")
                },
                TAG: function(aM, aL) {
                    return aM[1].toLowerCase()
                },
                CHILD: function(aL) {
                    if (aL[1] === "nth") {
                        var aM = /(-?)(\d*)n((?:\+|-)?\d*)/.exec(aL[2] === "even" && "2n" || aL[2] === "odd" && "2n+1" || !/\D/.test(aL[2]) && "0n+" + aL[2] || aL[2]);
                        aL[2] = (aM[1] + (aM[2] || 1)) - 0;
                        aL[3] = aM[3] - 0
                    }
                    aL[0] = aF++;
                    return aL
                },
                ATTR: function(aP, aM, aN, aL, aQ, aR) {
                    var aO = aP[1].replace(/\\/g, "");
                    if (!aR && aB.attrMap[aO]) {
                        aP[1] = aB.attrMap[aO]
                    }
                    if (aP[2] === "~=") {
                        aP[4] = " " + aP[4] + " "
                    }
                    return aP
                },
                PSEUDO: function(aP, aM, aN, aL, aQ) {
                    if (aP[1] === "not") {
                        if ((aE.exec(aP[3]) || "").length > 1 || /^\w/.test(aP[3])) {
                            aP[3] = S(aP[3], null, null, aM)
                        } else {
                            var aO = S.filter(aP[3], aM, aN, true ^ aQ);
                            if (!aN) {
                                aL.push.apply(aL, aO)
                            }
                            return false
                        }
                    } else {
                        if (aB.match.POS.test(aP[0]) || aB.match.CHILD.test(aP[0])) {
                            return true
                        }
                    }
                    return aP
                },
                POS: function(aL) {
                    aL.unshift(true);
                    return aL
                }
            },
            filters: {
                enabled: function(aL) {
                    return aL.disabled === false && aL.type !== "hidden"
                },
                disabled: function(aL) {
                    return aL.disabled === true
                },
                checked: function(aL) {
                    return aL.checked === true
                },
                selected: function(aL) {
                    aL.parentNode.selectedIndex;
                    return aL.selected === true
                },
                parent: function(aL) {
                    return !! aL.firstChild
                },
                empty: function(aL) {
                    return ! aL.firstChild
                },
                has: function(aN, aM, aL) {
                    return !! S(aL[3], aN).length
                },
                header: function(aL) {
                    return /h\d/i.test(aL.nodeName)
                },
                text: function(aL) {
                    return "text" === aL.type
                },
                radio: function(aL) {
                    return "radio" === aL.type
                },
                checkbox: function(aL) {
                    return "checkbox" === aL.type
                },
                file: function(aL) {
                    return "file" === aL.type
                },
                password: function(aL) {
                    return "password" === aL.type
                },
                submit: function(aL) {
                    return "submit" === aL.type
                },
                image: function(aL) {
                    return "image" === aL.type
                },
                reset: function(aL) {
                    return "reset" === aL.type
                },
                button: function(aL) {
                    return "button" === aL.type || aL.nodeName.toLowerCase() === "button"
                },
                input: function(aL) {
                    return /input|select|textarea|button/i.test(aL.nodeName)
                }
            },
            setFilters: {
                first: function(aM, aL) {
                    return aL === 0
                },
                last: function(aN, aM, aL, aO) {
                    return aM === aO.length - 1
                },
                even: function(aM, aL) {
                    return aL % 2 === 0
                },
                odd: function(aM, aL) {
                    return aL % 2 === 1
                },
                lt: function(aN, aM, aL) {
                    return aM < aL[3] - 0
                },
                gt: function(aN, aM, aL) {
                    return aM > aL[3] - 0
                },
                nth: function(aN, aM, aL) {
                    return aL[3] - 0 === aM
                },
                eq: function(aN, aM, aL) {
                    return aL[3] - 0 === aM
                }
            },
            filter: {
                PSEUDO: function(aR, aN, aO, aS) {
                    var aM = aN[1],
                    aP = aB.filters[aM];
                    if (aP) {
                        return aP(aR, aO, aN, aS)
                    } else {
                        if (aM === "contains") {
                            return (aR.textContent || aR.innerText || K([aR]) || "").indexOf(aN[3]) >= 0
                        } else {
                            if (aM === "not") {
                                var aQ = aN[3];
                                for (var aO = 0,
                                aL = aQ.length; aO < aL; aO++) {
                                    if (aQ[aO] === aR) {
                                        return false
                                    }
                                }
                                return true
                            } else {
                                throw "Syntax error, unrecognized expression: " + aM
                            }
                        }
                    }
                },
                CHILD: function(aL, aO) {
                    var aR = aO[1],
                    aM = aL;
                    switch (aR) {
                    case "only":
                    case "first":
                        while ((aM = aM.previousSibling)) {
                            if (aM.nodeType === 1) {
                                return false
                            }
                        }
                        if (aR === "first") {
                            return true
                        }
                        aM = aL;
                    case "last":
                        while ((aM = aM.nextSibling)) {
                            if (aM.nodeType === 1) {
                                return false
                            }
                        }
                        return true;
                    case "nth":
                        var aN = aO[2],
                        aU = aO[3];
                        if (aN === 1 && aU === 0) {
                            return true
                        }
                        var aQ = aO[0],
                        aT = aL.parentNode;
                        if (aT && (aT.sizcache !== aQ || !aL.nodeIndex)) {
                            var aP = 0;
                            for (aM = aT.firstChild; aM; aM = aM.nextSibling) {
                                if (aM.nodeType === 1) {
                                    aM.nodeIndex = ++aP
                                }
                            }
                            aT.sizcache = aQ
                        }
                        var aS = aL.nodeIndex - aU;
                        if (aN === 0) {
                            return aS === 0
                        } else {
                            return (aS % aN === 0 && aS / aN >= 0)
                        }
                    }
                },
                ID: function(aM, aL) {
                    return aM.nodeType === 1 && aM.getAttribute("id") === aL
                },
                TAG: function(aM, aL) {
                    return (aL === "*" && aM.nodeType === 1) || aM.nodeName.toLowerCase() === aL
                },
                CLASS: function(aM, aL) {
                    return (" " + (aM.className || aM.getAttribute("class")) + " ").indexOf(aL) > -1
                },
                ATTR: function(aQ, aO) {
                    var aN = aO[1],
                    aL = aB.attrHandle[aN] ? aB.attrHandle[aN](aQ) : aQ[aN] != null ? aQ[aN] : aQ.getAttribute(aN),
                    aR = aL + "",
                    aP = aO[2],
                    aM = aO[4];
                    return aL == null ? aP === "!=": aP === "=" ? aR === aM: aP === "*=" ? aR.indexOf(aM) >= 0 : aP === "~=" ? (" " + aR + " ").indexOf(aM) >= 0 : !aM ? aR && aL !== false: aP === "!=" ? aR !== aM: aP === "^=" ? aR.indexOf(aM) === 0 : aP === "$=" ? aR.substr(aR.length - aM.length) === aM: aP === "|=" ? aR === aM || aR.substr(0, aM.length + 1) === aM + "-": false
                },
                POS: function(aP, aM, aN, aQ) {
                    var aL = aM[2],
                    aO = aB.setFilters[aL];
                    if (aO) {
                        return aO(aP, aN, aM, aQ)
                    }
                }
            }
        };
        var aA = aB.match.POS;
        for (var ax in aB.match) {
            aB.match[ax] = new RegExp(aB.match[ax].source + /(?![^\[]*\])(?![^\(]*\))/.source);
            aB.leftMatch[ax] = new RegExp(/(^(?:.|\r|\n)*?)/.source + aB.match[ax].source)
        }
        var aD = function(aM, aL) {
            aM = Array.prototype.slice.call(aM, 0);
            if (aL) {
                aL.push.apply(aL, aM);
                return aL
            }
            return aM
        };
        try {
            Array.prototype.slice.call(document.documentElement.childNodes, 0)
        } catch(aK) {
            aD = function(aP, aO) {
                var aM = aO || [];
                if (aH.call(aP) === "[object Array]") {
                    Array.prototype.push.apply(aM, aP)
                } else {
                    if (typeof aP.length === "number") {
                        for (var aN = 0,
                        aL = aP.length; aN < aL; aN++) {
                            aM.push(aP[aN])
                        }
                    } else {
                        for (var aN = 0; aP[aN]; aN++) {
                            aM.push(aP[aN])
                        }
                    }
                }
                return aM
            }
        }
        var aG;
        if (document.documentElement.compareDocumentPosition) {
            aG = function(aM, aL) {
                if (!aM.compareDocumentPosition || !aL.compareDocumentPosition) {
                    if (aM == aL) {
                        az = true
                    }
                    return aM.compareDocumentPosition ? -1 : 1
                }
                var aN = aM.compareDocumentPosition(aL) & 4 ? -1 : aM === aL ? 0 : 1;
                if (aN === 0) {
                    az = true
                }
                return aN
            }
        } else {
            if ("sourceIndex" in document.documentElement) {
                aG = function(aM, aL) {
                    if (!aM.sourceIndex || !aL.sourceIndex) {
                        if (aM == aL) {
                            az = true
                        }
                        return aM.sourceIndex ? -1 : 1
                    }
                    var aN = aM.sourceIndex - aL.sourceIndex;
                    if (aN === 0) {
                        az = true
                    }
                    return aN
                }
            } else {
                if (document.createRange) {
                    aG = function(aO, aM) {
                        if (!aO.ownerDocument || !aM.ownerDocument) {
                            if (aO == aM) {
                                az = true
                            }
                            return aO.ownerDocument ? -1 : 1
                        }
                        var aN = aO.ownerDocument.createRange(),
                        aL = aM.ownerDocument.createRange();
                        aN.setStart(aO, 0);
                        aN.setEnd(aO, 0);
                        aL.setStart(aM, 0);
                        aL.setEnd(aM, 0);
                        var aP = aN.compareBoundaryPoints(Range.START_TO_END, aL);
                        if (aP === 0) {
                            az = true
                        }
                        return aP
                    }
                }
            }
        }
        function K(aL) {
            var aM = "",
            aO;
            for (var aN = 0; aL[aN]; aN++) {
                aO = aL[aN];
                if (aO.nodeType === 3 || aO.nodeType === 4) {
                    aM += aO.nodeValue
                } else {
                    if (aO.nodeType !== 8) {
                        aM += K(aO.childNodes)
                    }
                }
            }
            return aM
        } (function() {
            var aM = document.createElement("div"),
            aN = "script" + (new Date).getTime();
            aM.innerHTML = "<a name='" + aN + "'/>";
            var aL = document.documentElement;
            aL.insertBefore(aM, aL.firstChild);
            if (document.getElementById(aN)) {
                aB.find.ID = function(aP, aQ, aR) {
                    if (typeof aQ.getElementById !== "undefined" && !aR) {
                        var aO = aQ.getElementById(aP[1]);
                        return aO ? aO.id === aP[1] || typeof aO.getAttributeNode !== "undefined" && aO.getAttributeNode("id").nodeValue === aP[1] ? [aO] : q: []
                    }
                };
                aB.filter.ID = function(aQ, aO) {
                    var aP = typeof aQ.getAttributeNode !== "undefined" && aQ.getAttributeNode("id");
                    return aQ.nodeType === 1 && aP && aP.nodeValue === aO
                }
            }
            aL.removeChild(aM);
            aL = aM = null
        })();
        (function() {
            var aL = document.createElement("div");
            aL.appendChild(document.createComment(""));
            if (aL.getElementsByTagName("*").length > 0) {
                aB.find.TAG = function(aM, aQ) {
                    var aP = aQ.getElementsByTagName(aM[1]);
                    if (aM[1] === "*") {
                        var aO = [];
                        for (var aN = 0; aP[aN]; aN++) {
                            if (aP[aN].nodeType === 1) {
                                aO.push(aP[aN])
                            }
                        }
                        aP = aO
                    }
                    return aP
                }
            }
            aL.innerHTML = "<a href='#'></a>";
            if (aL.firstChild && typeof aL.firstChild.getAttribute !== "undefined" && aL.firstChild.getAttribute("href") !== "#") {
                aB.attrHandle.href = function(aM) {
                    return aM.getAttribute("href", 2)
                }
            }
            aL = null
        })();
        if (document.querySelectorAll) { (function() {
                var aL = S,
                aN = document.createElement("div");
                aN.innerHTML = "<p class='TEST'></p>";
                if (aN.querySelectorAll && aN.querySelectorAll(".TEST").length === 0) {
                    return
                }
                S = function(aR, aQ, aO, aP) {
                    aQ = aQ || document;
                    if (!aP && aQ.nodeType === 9 && !aw(aQ)) {
                        try {
                            return aD(aQ.querySelectorAll(aR), aO)
                        } catch(aS) {}
                    }
                    return aL(aR, aQ, aO, aP)
                };
                for (var aM in aL) {
                    S[aM] = aL[aM]
                }
                aN = null
            })()
        } (function() {
            var aL = document.createElement("div");
            aL.innerHTML = "<div class='test e'></div><div class='test'></div>";
            if (!aL.getElementsByClassName || aL.getElementsByClassName("e").length === 0) {
                return
            }
            aL.lastChild.className = "e";
            if (aL.getElementsByClassName("e").length === 1) {
                return
            }
            aB.order.splice(1, 0, "CLASS");
            aB.find.CLASS = function(aM, aN, aO) {
                if (typeof aN.getElementsByClassName !== "undefined" && !aO) {
                    return aN.getElementsByClassName(aM[1])
                }
            };
            aL = null
        })();
        function E(aM, aR, aQ, aU, aS, aT) {
            for (var aO = 0,
            aN = aU.length; aO < aN; aO++) {
                var aL = aU[aO];
                if (aL) {
                    aL = aL[aM];
                    var aP = false;
                    while (aL) {
                        if (aL.sizcache === aQ) {
                            aP = aU[aL.sizset];
                            break
                        }
                        if (aL.nodeType === 1 && !aT) {
                            aL.sizcache = aQ;
                            aL.sizset = aO
                        }
                        if (aL.nodeName.toLowerCase() === aR) {
                            aP = aL;
                            break
                        }
                        aL = aL[aM]
                    }
                    aU[aO] = aP
                }
            }
        }
        function aJ(aM, aR, aQ, aU, aS, aT) {
            for (var aO = 0,
            aN = aU.length; aO < aN; aO++) {
                var aL = aU[aO];
                if (aL) {
                    aL = aL[aM];
                    var aP = false;
                    while (aL) {
                        if (aL.sizcache === aQ) {
                            aP = aU[aL.sizset];
                            break
                        }
                        if (aL.nodeType === 1) {
                            if (!aT) {
                                aL.sizcache = aQ;
                                aL.sizset = aO
                            }
                            if (typeof aR !== "string") {
                                if (aL === aR) {
                                    aP = true;
                                    break
                                }
                            } else {
                                if (S.filter(aR, [aL]).length > 0) {
                                    aP = aL;
                                    break
                                }
                            }
                        }
                        aL = aL[aM]
                    }
                    aU[aO] = aP
                }
            }
        }
        var aC = document.compareDocumentPosition ?
        function(aM, aL) {
            return aM.compareDocumentPosition(aL) & 16
        }: function(aM, aL) {
            return aM !== aL && (aM.contains ? aM.contains(aL) : true)
        };
        var aw = function(aL) {
            var aM = (aL ? aL.ownerDocument || aL: 0).documentElement;
            return aM ? aM.nodeName !== "HTML": false
        };
        var aI = function(aL, aS) {
            var aO = [],
            aP = "",
            aQ,
            aN = aS.nodeType ? [aS] : aS;
            while ((aQ = aB.match.PSEUDO.exec(aL))) {
                aP += aQ[0];
                aL = aL.replace(aB.match.PSEUDO, "")
            }
            aL = aB.relative[aL] ? aL + "*": aL;
            for (var aR = 0,
            aM = aN.length; aR < aM; aR++) {
                S(aL, aN[aR], aO)
            }
            return S.filter(aP, aO)
        };
        return S
    })();
    g.lang = {
        code: "es",
        of: "de",
        loading: "cargando",
        cancel: "Cancelar",
        next: "Siguiente",
        previous: "Anterior",
        play: "Reproducir",
        pause: "Pausa",
        close: "Cerrar",
        errors: {
            single: 'Debes instalar el plugin <a href="{0}">{1}</a> en el navegador para ver este contenido.',
            shared: 'Debes instalar el <a href="{0}">{1}</a> y el <a href="{2}">{3}</a> en el navegador para ver este contenido.',
            either: 'Debes instalar o bien el <a href="{0}">{1}</a> o el <a href="{2}">{3}</a> en el navegador para ver este contenido.'
        }
    };
    g.iframe = function(K, S) {
        this.obj = K;
        this.id = S;
        var E = ai("sb-overlay");
        this.height = K.height ? parseInt(K.height, 10) : E.offsetHeight;
        this.width = K.width ? parseInt(K.width, 10) : E.offsetWidth
    };
    g.iframe.prototype = {
        append: function(E, S) {
            var K = '<iframe id="' + this.id + '" name="' + this.id + '" height="100%" width="100%" frameborder="0" marginwidth="0" marginheight="0" style="visibility:hidden" onload="this.style.visibility=\'visible\'" scrolling="auto"';
            if (g.isIE) {
                K += ' allowtransparency="true"';
                if (g.isIE6) {
                    K += " src=\"javascript:false;document.write('');\""
                }
            }
            K += "></iframe>";
            E.innerHTML = K
        },
        remove: function() {
            var E = ai(this.id);
            if (E) {
                A(E);
                if (g.isGecko) {
                    delete V.frames[this.id]
                }
            }
        },
        onLoad: function() {
            var E = g.isIE ? ai(this.id).contentWindow: V.frames[this.id];
            E.location.href = this.obj.content
        }
    };
    var an = false,
    B = [],
    J = ["sb-nav-close", "sb-nav-next", "sb-nav-play", "sb-nav-pause", "sb-nav-previous"],
    H,
    aj,
    w,
    R = true;
    function ag(S, aF, aC, aA, aG) {
        var E = (aF == "opacity"),
        aB = E ? g.setOpacity: function(aH, aI) {
            aH.style[aF] = "" + aI + "px"
        };
        if (aA == 0 || (!E && !g.options.animate) || (E && !g.options.animateFade)) {
            aB(S, aC);
            if (aG) {
                aG()
            }
            return
        }
        var aD = parseFloat(g.getStyle(S, aF)) || 0;
        var aE = aC - aD;
        if (aE == 0) {
            if (aG) {
                aG()
            }
            return
        }
        aA *= 1000;
        var aw = Z(),
        az = g.ease,
        ay = aw + aA,
        ax;
        var K = setInterval(function() {
            ax = Z();
            if (ax >= ay) {
                clearInterval(K);
                K = null;
                aB(S, aC);
                if (aG) {
                    aG()
                }
            } else {
                aB(S, aD + az((ax - aw) / aA) * aE)
            }
        },
        10)
    }
    function L() {
        H.style.height = g.getWindowSize("Height") + "px";
        H.style.width = g.getWindowSize("Width") + "px"
    }
    function af() {
        H.style.top = document.documentElement.scrollTop + "px";
        H.style.left = document.documentElement.scrollLeft + "px"
    }
    function z(E) {
        if (E) {
            ae(B,
            function(K, S) {
                S[0].style.visibility = S[1] || ""
            })
        } else {
            B = [];
            ae(g.options.troubleElements,
            function(S, K) {
                ae(document.getElementsByTagName(K),
                function(aw, ax) {
                    B.push([ax, ax.style.visibility]);
                    ax.style.visibility = "hidden"
                })
            })
        }
    }
    function y(S, E) {
        var K = ai("sb-nav-" + S);
        if (K) {
            K.style.display = E ? "": "none"
        }
    }
    function n(E, ay) {
        var ax = ai("sb-loading"),
        S = g.getCurrent().player,
        aw = (S == "img" || S == "html");
        if (E) {
            g.setOpacity(ax, 0);
            ax.style.display = "block";
            var K = function() {
                g.clearOpacity(ax);
                if (ay) {
                    ay()
                }
            };
            if (aw) {
                ag(ax, "opacity", 1, g.options.fadeDuration, K)
            } else {
                K()
            }
        } else {
            var K = function() {
                ax.style.display = "none";
                g.clearOpacity(ax);
                if (ay) {
                    ay()
                }
            };
            if (aw) {
                ag(ax, "opacity", 0, g.options.fadeDuration, K)
            } else {
                K()
            }
        }
    }
    function av(aD) {
        var ay = g.getCurrent();
        ai("sb-title-inner").innerHTML = ay.title || "";
        var aE, aA, K, aF, aB;
        if (g.options.displayNav) {
            aE = true;
            var aC = g.gallery.length;
            if (aC > 1) {
                if (g.options.continuous) {
                    aA = aB = true
                } else {
                    aA = (aC - 1) > g.current;
                    aB = g.current > 0
                }
            }
            if (g.options.slideshowDelay > 0 && g.hasNext()) {
                aF = !g.isPaused();
                K = !aF
            }
        } else {
            aE = aA = K = aF = aB = false
        }
        y("close", aE);
        y("next", aA);
        y("play", K);
        y("pause", aF);
        y("previous", aB);
        var E = "";
        if (g.options.displayCounter && g.gallery.length > 1) {
            var aC = g.gallery.length;
            if (g.options.counterType == "skip") {
                var ax = 0,
                aw = aC,
                S = parseInt(g.options.counterLimit) || 0;
                if (S < aC && S > 2) {
                    var az = Math.floor(S / 2);
                    ax = g.current - az;
                    if (ax < 0) {
                        ax += aC
                    }
                    aw = g.current + (S - az);
                    if (aw > aC) {
                        aw -= aC
                    }
                }
                while (ax != aw) {
                    if (ax == aC) {
                        ax = 0
                    }
                    E += '<a onclick="Shadowbox.change(' + ax + ');"';
                    if (ax == g.current) {
                        E += ' class="sb-counter-current"'
                    }
                    E += ">" + (++ax) + "</a>"
                }
            } else {
                E = [g.current + 1, g.lang.of, aC].join(" ")
            }
        }
        ai("sb-counter").innerHTML = E;
        aD()
    }
    function v(aw) {
        var E = ai("sb-title-inner"),
        S = ai("sb-info-inner"),
        K = 0.35;
        E.style.visibility = S.style.visibility = "";
        if (E.innerHTML != "") {
            ag(E, "marginTop", 0, K)
        }
        ag(S, "marginTop", 0, K, aw)
    }
    function ad(S, aB) {
        var az = ai("sb-title"),
        E = ai("sb-info"),
        aw = az.offsetHeight,
        ax = E.offsetHeight,
        ay = ai("sb-title-inner"),
        aA = ai("sb-info-inner"),
        K = (S ? 0.35 : 0);
        ag(ay, "marginTop", aw, K);
        ag(aA, "marginTop", ax * -1, K,
        function() {
            ay.style.visibility = aA.style.visibility = "hidden";
            aB()
        })
    }
    function G(E, aw, K, ay) {
        var ax = ai("sb-wrapper-inner"),
        S = (K ? g.options.resizeDuration: 0);
        ag(w, "top", aw, S);
        ag(ax, "height", E, S, ay)
    }
    function u(E, aw, K, ax) {
        var S = (K ? g.options.resizeDuration: 0);
        ag(w, "left", aw, S);
        ag(w, "width", E, S, ax)
    }
    function U(aB, S) {
        var ax = ai("sb-body-inner"),
        aB = parseInt(aB),
        S = parseInt(S),
        K = w.offsetHeight - ax.offsetHeight,
        E = w.offsetWidth - ax.offsetWidth,
        az = aj.offsetHeight,
        aA = aj.offsetWidth,
        ay = parseInt(g.options.viewportPadding) || 20,
        aw = (g.player && g.options.handleOversize != "drag");
        return g.setDimensions(aB, S, az, aA, K, E, ay, aw)
    }
    var k = {};
    k.markup = '<div id="sb-container"><div id="sb-overlay"></div><div id="sb-wrapper"><div id="sb-title"><div id="sb-title-inner"></div></div><div id="sb-wrapper-inner"><div id="sb-body"><div id="sb-body-inner"></div><div id="sb-loading"><div id="sb-loading-inner"><span>{loading}</span></div></div></div></div><div id="sb-info"><div id="sb-info-inner"><div id="sb-counter"></div><div id="sb-nav"><a id="sb-nav-close" title="{close}" onclick="Shadowbox.close()"></a><a id="sb-nav-next" title="{next}" onclick="Shadowbox.next()"></a><a id="sb-nav-play" title="{play}" onclick="Shadowbox.play()"></a><a id="sb-nav-pause" title="{pause}" onclick="Shadowbox.pause()"></a><a id="sb-nav-previous" title="{previous}" onclick="Shadowbox.previous()"></a></div></div></div></div></div>';
    k.options = {
        animSequence: "sync",
        counterLimit: 10,
        counterType: "default",
        displayCounter: true,
        displayNav: true,
        fadeDuration: 0.35,
        initialHeight: 160,
        initialWidth: 320,
        modal: false,
        overlayColor: "#000",
        overlayOpacity: 0.5,
        resizeDuration: 0.35,
        showOverlay: true,
        troubleElements: ["select", "object", "embed", "canvas"]
    };
    k.init = function() {
        g.appendHTML(document.body, t(k.markup, g.lang));
        k.body = ai("sb-body-inner");
        H = ai("sb-container");
        aj = ai("sb-overlay");
        w = ai("sb-wrapper");
        if (!N) {
            H.style.position = "absolute"
        }
        if (!am) {
            var S, E, K = /url\("(.*\.png)"\)/;
            ae(J,
            function(ax, ay) {
                S = ai(ay);
                if (S) {
                    E = g.getStyle(S, "backgroundImage").match(K);
                    if (E) {
                        S.style.backgroundImage = "none";
                        S.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,src=" + E[1] + ",sizingMethod=scale);"
                    }
                }
            })
        }
        var aw;
        j(V, "resize",
        function() {
            if (aw) {
                clearTimeout(aw);
                aw = null
            }
            if (x) {
                aw = setTimeout(k.onWindowResize, 10)
            }
        })
    };
    k.onOpen = function(E, S) {
        R = false;
        H.style.display = "block";
        L();
        var K = U(g.options.initialHeight, g.options.initialWidth);
        G(K.innerHeight, K.top);
        u(K.width, K.left);
        if (g.options.showOverlay) {
            aj.style.backgroundColor = g.options.overlayColor;
            g.setOpacity(aj, 0);
            if (!g.options.modal) {
                j(aj, "click", g.close)
            }
            an = true
        }
        if (!N) {
            af();
            j(V, "scroll", af)
        }
        z();
        H.style.visibility = "visible";
        if (an) {
            ag(aj, "opacity", g.options.overlayOpacity, g.options.fadeDuration, S)
        } else {
            S()
        }
    };
    k.onLoad = function(K, E) {
        n(true);
        while (k.body.firstChild) {
            A(k.body.firstChild)
        }
        ad(K,
        function() {
            if (!x) {
                return
            }
            if (!K) {
                w.style.visibility = "visible"
            }
            av(E)
        })
    };
    k.onReady = function(aw) {
        if (!x) {
            return
        }
        var K = g.player,
        S = U(K.height, K.width);
        var E = function() {
            v(aw)
        };
        switch (g.options.animSequence) {
        case "hw":
            G(S.innerHeight, S.top, true,
            function() {
                u(S.width, S.left, true, E)
            });
            break;
        case "wh":
            u(S.width, S.left, true,
            function() {
                G(S.innerHeight, S.top, true, E)
            });
            break;
        default:
            u(S.width, S.left, true);
            G(S.innerHeight, S.top, true, E)
        }
    };
    k.onShow = function(E) {
        n(false, E);
        R = true
    };
    k.onClose = function() {
        if (!N) {
            a(V, "scroll", af)
        }
        a(aj, "click", g.close);
        w.style.visibility = "hidden";
        var E = function() {
            H.style.visibility = "hidden";
            H.style.display = "none";
            z(true)
        };
        if (an) {
            ag(aj, "opacity", 0, g.options.fadeDuration, E)
        } else {
            E()
        }
    };
    k.onPlay = function() {
        y("play", false);
        y("pause", true)
    };
    k.onPause = function() {
        y("pause", false);
        y("play", true)
    };
    k.onWindowResize = function() {
        if (!R) {
            return
        }
        L();
        var E = g.player,
        K = U(E.height, E.width);
        u(K.width, K.left);
        G(K.innerHeight, K.top);
        if (E.onWindowResize) {
            E.onWindowResize()
        }
    };
    g.skin = k;
    V.Shadowbox = g
})(window);