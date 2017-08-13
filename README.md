# BvisonlInactivityBundle

## Introduction

The purpose of this bundle is to provide a "better" way of handling the log in session of an User in Symfony2. While handling multiple tabs opened and client idletime.

I have to thank (https://github.com/JillElaine/jquery-idleTimeout) for giving me the ideas of how to implement this. Sadly, that bundle was not working properly for me and also it just covered the client part of the issue that I was having so I decided to just make something that merged both client and server.


## Pre-requisites
This bundle depends on store.js ==> https://github.com/marcuswestin/store.js/ 

## Installation
1. Install via composer
```
composer require bvisonl/inactivitybundle 1.x
```

2. Add to the AppKernel
```
 public function registerBundles()
    {
        $bundles = array(
        ...
        new Bvisonl\InactivityBundle\BvisonlInactivityBundle(),
        ...           
        );
    }
```

3. Define the *bvisonl_session_timeout* parameter in your parameters.yml
```
parameters:
    bvisonl_session_lifetime: 300 # 5 minutes
```

If you are using the *cookie_lifetime*, set it up to a very high value in your config.yml:
```
session:
    ...        
    cookie_lifetime: 300000000000 # Set this value high enough and let the bundle handle the session inactivity
```

Also, if you intend to use this parameter in your HTML (as shown below). You must define it in your twig globals in your config.yml:
```
# Twig Configuration
twig:
    ...
    globals:        
        bvisonl_session_lifetime: "%bvisonl_session_lifetime%"
```

4. Add the javascript somewhere in your HTML (i.e. in the base.html.twig)
```
{% javascripts '@BvisonlInactivityBundle/Resources/public/js/inactivity.handler.js' %}
    <script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}
```

5. Configure the JS plugin to check for user activity
```
<!-- InactivityHandler Configuration -->
<script type="text/javascript">
    $(document).ready(function() {
        $.fn.bvisonlInactivityHandler({
            // This is the default configuration, in reality no parameter is required but check that the defaults are ok for you
            sessionTimeout: {{ bvisonl_session_lifetime }}, // Default is 300 (5 minutes)
            logoutUrl: "{{ path('fos_user_security_logout') }}", // Default is "/"
            pingServer: true, // Default is false
            pingUrl: '/bvisonl/inactivity/ping', // Url to ping to keep session alive in the server, MUST RETURN A 204 response
            pingInterval: {{ bvisonl_session_lifetime/2 }}, // Default is 150
            events: 'click keypress scroll wheel mousewheel mousemove keyup', // Default events to check on the browser
        });
    })
</script>
```

## How it works

Basically, if configured as shown above, the process divides itself in 2:

#### ~ Server

On the server, there is a listener that keeps...well...listening the last time a session was used, and compares it to the session_lifetime parameter. If the session should be destroyed, then it will be destroyed. Simple enough for the server.

#### ~ Client

In Symfony2, if you do not affect the session for a configurable amount of time, the session will be destroyed and you won't be able to perform any requests to any secured route. However, let's say that an user is analyzing a very complex report on screen scrolling around and you have the lifetime of your session to 5 minutes, when the user finishes working on the report and attempts to move around the app. On the next request, Symfony2 will re-route the user to the login screen. 

To solve this, the plugin takes care of tracking the configured events and maintaining timers (using the store.js). Also, in conjunction with the pingServer functionality, the client will keep pinging the server (which should return a 204, 200 is no good since Symfony2 may return code 200 when redirecting the request to the login screen, if you use the default settings you shouldn't worry about this) everytime the client pings the server, it tells Symfony2's session that the user is still active therefore it won't log them off.


## Todo

- Maybe: Add dialog window before logging out

