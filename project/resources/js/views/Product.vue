<template>
    <div>
        <div v-if="this.owner === this.uid1">
            <a href="/user/messages/inbox">
                 <i class="icofont-ui-chat"></i>View Inbox
            </a>
        </div>

        <div v-else>
            <a v-on:click="messageTheSeller" href="#">
                 <i class="icofont-ui-chat"></i>{{ messageSeller ? 'Close Chat' : 'Chat' }}
            </a>
            <div id="chat-box" v-if="messageSeller">
                <comet-chat-messages :type="type"
                    :logged-in-user="authUser"
                    :item="productOwner"
                    :call-message="callMessage"
                    :tab="tab"
                    :action-from-listener="actionFromListener"
                    @action="actionHandler" ></comet-chat-messages>
                <comet-chat-incoming-call :theme="themeValue" @action="actionHandler" />
                <comet-chat-outgoing-call
                    :item="item"
                    :type="type"
                    :theme="themeValue"
                    :incoming-call="incomingCall"
                    :outgoing-call="outgoingCall"
                    :logged-in-user="authUser"
                    @action="actionHandler"
                />
            </div>
        </div>

    </div>
</template>

<script>
import { CometChatManager } from "../cometchat-pro-vue-ui-kit/CometChatWorkspace/src/util/controller";
import { cometChatScreens } from "../cometchat-pro-vue-ui-kit/CometChatWorkspace/src/mixins/";
import { CometChatIncomingCall, CometChatOutgoingCall } from "../cometchat-pro-vue-ui-kit/CometChatWorkspace/src/components/Calls";
import { CometChatMessages } from "../cometchat-pro-vue-ui-kit/CometChatWorkspace/src";
import { theme } from "../cometchat-pro-vue-ui-kit/CometChatWorkspace/src/resources/theme";
import {CometChat} from "@cometchat-pro/chat";
import moment from "moment"

export default {
    name: "Product",
    mixins: [cometChatScreens],
    props: ['id', 'uid1', 'user','owner'],
    components: {
        CometChatMessages,
        CometChatIncomingCall,
        CometChatOutgoingCall,
    },
    data() {
        return {
            item: {},
            type: "user",
            tab: "conversations",
            incomingCall: null,
            outgoingCall: null,
            loggedInUser: null,
            callMessage: {},
            product: {},
            authUser: {},
            productOwner: {},
            actionFromListener: {},
            messageSeller: false
        }
    },
    async mounted() {
        this.authUser = await CometChat.getUser(this.uid1);
        this.productOwner = await CometChat.getUser(this.owner);
        this.item = this.productOwner;

        // axios.get(`/product/${this.id}`)
        //     .then((response) => {
        //         console.log(response.data.product);
        //         this.product = response.data.product;
        //     })
        //     .catch((error) => {
        //         console.log(error);
        //     });
    },
    computed: {
        /**
         * Theme computed using default theme and theme coming from prop.
         */
        themeValue() {
            return Object.assign({}, theme, this.theme);
        },
    },
    methods: {
        actionHandler({
        action,
        call,
        incomingCall,
        rejectedCall,
        }) {
            switch (action) {
                case "audioCall":
                    this.audioCall();
                    break;
                case "videoCall":
                    this.videoCall();
                    break;
                case "acceptIncomingCall":
                    this.acceptIncomingCall(incomingCall);
                    break;
                case "acceptedIncomingCall":
                    this.appendCallMessage(call);
                    break;
                case "rejectedIncomingCall":
                    this.rejectedIncomingCall(incomingCall, rejectedCall);
                    break;
                case "outgoingCallRejected":
                case "outgoingCallCancelled":
                case "callEnded":
                    this.outgoingCallEnded(call);
                    break;
                case "userJoinedCall":
                case "userLeftCall":
                    this.appendCallMessage(call);
                    break;
                case "listenerData":
                    this.actionFromListener = { action: action, messages: [...messages] };
                default:
                    break;
            }
        },
        messageTheSeller() {

            this.messageSeller = !this.messageSeller;
            if(this.messageSeller){

                var receiverID = this.owner;
                var messageText = window.location.href;
                var receiverType = CometChat.RECEIVER_TYPE.USER;
                var textMessage = new CometChat.TextMessage(
                    receiverID,
                    messageText,
                    receiverType
                );

                CometChat.sendMessage(textMessage).then(
                    message => {
                        // console.log("Message sent successfully:", message);

                    },
                    error => {
                        console.log("Message sending failed with error:", error);
                    }
                );
            }
        },
        viewInbox() {
            window.location.href = `/user/messages/inbox`;
        },
        getCreatedDate(date) {
            return moment(date).format('MMMM d, Y')
        }
    },
    beforeMount() {
        (async () => {
            try {
                this.loggedInUser = await new CometChatManager().getLoggedInUser();
            } catch (error) {
                this.logError(
                "[Product.vue] getLoggedInUser error",
                error
                );
            }
        })();
    },
}
</script>

<style scoped>
#chat-box {
    width: 340px;
    position: absolute;
    height: 400px;
    border-style: solid;
    border-width: thin;
    border-radius: 1%;
    padding: 1px 1px 1px 1px;
    background-color: white;
    left: 15px;
    z-index: 999;
}

.product-container{
    display:flex;
    flex-direction:row;
    justify-content:space-between;
    border-bottom:1px solid #ececec;
    padding:10px 0
}
.sub-text{
    color:#909090
}
.icon-container .fab{
    font-size:45px
}

.emoji-mart .vue-recycle-scroller.ready .vue-recycle-scroller__item-view{
    position: relative!important;
}
#chat-box > div > .vue-recycle-scroller__item-view{
    position: relative!important;
}

</style>
