/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');

// window.Vue = require('vue');

import Vue from 'vue'

import { CometChat } from "@cometchat-pro/chat/CometChat";

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

let cometChatAppSetting = new CometChat.AppSettingsBuilder()
    .subscribePresenceForAllUsers()
    // .setRegion(process.env.MIX_COMMETCHAT_APP_REGION)
    .setRegion('us')
    .build();

// Vue.component('passport-clients', require('./components/passport/Clients.vue').default);

// Vue.component(
//     'passport-authorized-clients',
//     require('./components/passport/AuthorizedClients.vue').default
// );

// Vue.component(
//     'passport-personal-access-tokens',
//     require('./components/passport/PersonalAccessTokens.vue').default
// );

Vue.component(
    'example-component',
    require('./components/ExampleComponent.vue').default
);
Vue.component('login-component', require('./views/Login.vue').default);
Vue.component('logout-component', require('./views/Logout.vue').default);
Vue.component('product-component', require('./views/Product.vue').default);
Vue.component('inbox-component', require('./views/Inbox.vue').default);
Vue.component('realtime-message-component', require('./views/RealTimeMessage.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 *
 *
 */

CometChat.init(process.env.MIX_COMMETCHAT_APP_ID, cometChatAppSetting).then(
// CometChat.init('195072a3cf23c2a5', cometChatAppSetting).then(
    () => {

        const app = new Vue({
            el: '#app',
        });
    },
    (error) => {
        console.log("Initialization failed with error:", error);
    }
);
