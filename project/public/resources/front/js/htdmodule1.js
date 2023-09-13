var appID = "195072a3cf23c2a5";
var region = "us";

var appSetting = new CometChat.AppSettingsBuilder().subscribePresenceForAllUsers().setRegion(region).build();
CometChat.init(appID, appSetting).then(
() => {
    console.log("Initialization completed successfully");
    // You can now call login function.
},
error => {
    console.log("Initialization failed with error:", error);
    // Check the reason for error and take appropriate action.
}
);
Vue.component('inbox-component', require('./views/Inbox.vue').default);

// $(function ($) {

//     console.log("Initialization completed successfully 123");

//     $(document).ready(function () {
//     });
// });

