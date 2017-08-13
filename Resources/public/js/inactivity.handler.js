$(document).ready(function() {
    $.fn.bvisonlInactivityHandler = function(options) {

        var now = new Date().getTime() / 1000;

        store.set("_bvisonl_forceLogout", false);
        store.set("_bvisonl_lastActivity", now);

        // These can be used to deactivate the handler at runtime
        // store.set("_bvisonl_deactivate", false);
        // store.set("_bvisonl_deactivate_ping", false);

        // Debug the handler
        // store.set("_bvisonl_debug", true);

        var defaultConfig = {
            logoutUrl: '/logout',
            sessionTimeout: 300, // 5 Minutes
            pingUrl: '/bvisonl/inactivity/ping',
            pingServer: false,
            pingInterval: 150, // 2.5 Minutes
            events: 'click keypress scroll wheel mousewheel mousemove keyup',
        }

        var config = $.extend(defaultConfig, options);

        // Logout Timer
        setInterval(function(){
            if(store.get('_bvisonl_deactivate') === true) {
                return;
            }
            if(store.get("_bvisonl_forceLogout") == true) {
                window.location.href = config.logoutUrl
            }
        }, 1000);

        // Inactivity Timer
        setInterval(function() {
            var idleTimeout = store.get('_bvisonl_lastActivity') + (config.sessionTimeout);
            var now = new Date().getTime() / 1000;
            if(store.get("_bvisonl_debug") === true) {
                console.log("Session Time Remaining:" + (now - idleTimeout));
            }

            if(idleTimeout < now) {
                store.set("_bvisonl_forceLogout", true);
            }
        }, 1000);

        // Server Ping Timer
        if(config.pingServer) {
            setInterval(function() {
                if(store.get('_bvisonl_deactivate_ping') === true) {
                    return;
                }
                if(store.get("_bvisonl_debug") === true) {
                    console.log("Pinging the server to keep session alive every: " + Math.min(2147483646, Math.round(config.pingInterval * 1000)) + " seconds");
                }
                $.ajax({
                    url: config.pingUrl,
                    complete: function(response){
                        // 204 in order to avoid server login redirection
                        // returning 200. Like in Symfony2 if you attempt
                        // to hit a secured url the ajax request will be redirected
                        // to the login url returning a 200 code... meaning, the server
                        // is logged out, but the client would stay logged in.
                        if(response.status != 204) {
                            store.set("_bvisonl_forceLogout", true);
                        }
                    }
                });
            }, Math.min(2147483646, Math.round(config.pingInterval * 1000)))
        }


        // Subscribe body to events
        $(document).on(config.events, function(e) {
            var now = new Date().getTime() / 1000;
            store.set("_bvisonl_lastActivity", now);
        });

    }
});