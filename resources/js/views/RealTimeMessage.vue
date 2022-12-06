<template>
    <div>
        <a href="/user/messages/inbox" class="cart carticon">
            <div class="icon">
                <i class="icofont-ui-messaging"></i>
                <span v-if="countMessage > 0" class="cart-quantity" id="cart-count"> {{ countMessage > 9 ? '9+' : countMessage }} </span>
            </div>
        </a>
    </div>
</template>
<script>
import {CometChat} from "@cometchat-pro/chat";
import { CometChatManager } from "../cometchat-pro-vue-ui-kit/CometChatWorkspace/src/util/controller";
import axios from 'axios'

export default {
    name: "RealTimeMessage",
    props: ['userInfo','username'],
    components: {
    },
    data() {
        return {
            countMessage: 0
        }
    },
    async mounted() {
        var UID = this.userInfo['uid'];
        var authKey =  this.userInfo['key'];
        var authToken =  this.userInfo['comet_token'];
        (async () => {
        try {
            const logged_user = await new CometChatManager().getLoggedInUser();
            if(logged_user == null){
                axios.get(`/user/comet/createuser/${this.userInfo['id']}`)
                    .then((response) => {
                        if(response != null){
                            // console.log('response create user', response);
                            CometChat.login(UID, authKey).then(
                                user => {
                                    this.updateCountMessage();
                                    this.listenMessage();
                                },
                                error => {
                                    console.log("Login failed with exception:", { error });
                                }
                            );

                        }
                    })
                    .catch((error) => {
                        console.log(error);
                    });

                // console.log('getUser for: ', UID);
                // CometChat.getUser(UID, authKey).then(
                //     user => {
                //         console.log('getUser success : ', UID);
                //         CometChat.login(UID, authKey).then(
                //             user => {
                //                 this.updateCountMessage();
                //                 this.listenMessage();
                //             },
                //             error => {
                //                 console.log("Login failed with exception:", { error });
                //             }
                //         );
                //     }, error => {
                //         console.log("User details fetching failed with error:", error);
                //         if(error.code == 'ERR_UID_NOT_FOUND')
                //         {
                //             var name = this.username;
                //             var user = new CometChat.User(UID);
                //             user.setName(name);
                //             CometChat.createUser(user, authKey).then(
                //                 user => {
                //                     console.log("createUser success");
                //                     CometChat.login(UID, authKey).then(
                //                         user => {
                //                             console.log("login success");
                //                             this.updateCountMessage();
                //                             this.listenMessage();
                //                         },
                //                         error => {
                //                             console.log("Login failed with exception:", { error });
                //                         }
                //                     );
                //                 },error => {
                //                     console.log("error", error);
                //                 }
                //             );
                //         }
                //     }
                // );
            }
            else{
                this.updateCountMessage();
                this.listenMessage();
            }
        } catch (error) {
            console.log("[CometChatUnified] getLoggedInUser error", error);
        }
        })();
    },
    methods: {
        viewInbox() {
            window.location.href = `/user/messages/inbox`;
        },
        updateCountMessage() {
            CometChat.getUnreadMessageCount().then(
                array => {
                    const object1 = array.users;
                    const object2 = array.groups;
                    var count1 = Object.values(object1).reduce((a, b) => a + b, 0);
                    var count2 = Object.values(object2).reduce((a, b) => a + b, 0);
                    this.countMessage = count1 + count2;
                },
                error => {
                    console.log("Error in getting message count", error);
                }
            );
        },
        listenMessage() {
            var listenerID = "UNIQUE_LISTENER_ID";
            CometChat.addMessageListener(
                listenerID,
                new CometChat.MessageListener({
                    onTextMessageReceived: textMessage => {

                    this.updateCountMessage();

                    // Handle text message
                    },
                    onMediaMessageReceived: mediaMessage => {
                    // Handle media message
                    },
                    onCustomMessageReceived: customMessage => {
                    // Handle custom message
                    }
                })
            );
        },
    }
}
</script>
