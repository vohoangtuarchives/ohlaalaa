<template>
    <div>

    </div>
</template>

<script>
import { CometChat } from "@cometchat-pro/chat";
import { CometChatManager } from "../cometchat-pro-vue-ui-kit/CometChatWorkspace/src/util/controller";

    export default {
        props: ['userInfo'],
        data() {
            return {
                email: "",
                password: '',
                showSpinner: false,
                token: '',
            };
        },
        mounted() {
            var UID = this.userInfo['uid'];
            var authKey =  this.userInfo['key'];
            (async () => {
            try {
                const logged_user = await new CometChatManager().getLoggedInUser();
                if(logged_user == null){
                    CometChat.login(UID, authKey).then(
                        user => {
                            // console.log("Login Successful:", { user });
                        },
                        error => {
                            console.log("Login failed with exception:", { error });
                        }
                    );
                }
            } catch (error) {
                this.logError("[CometChatUnified] getLoggedInUser error", error);
            }
            })();
        },
        methods: {
            logUserInToCometChat(token) {
                CometChat.login(token).then(
                    () => {
                        // console.log("successfully login user");
                    },
                    error => {
                        this.showSpinner = false;
                        alert("Whops. Something went wrong. This commonly happens when you enter a username that doesn't exist. Check the console for more information");
                        console.log("Login failed with error:", error.code);
                    }
                );
            },
        }
    };
</script>
