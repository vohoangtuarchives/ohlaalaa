<template>
    <div id="inbox-wrapper">
        <CometChatConversationListWithMessages />
    </div>
</template>

<script>
// import { CometChatConversationListWithMessages } from "../cometchat-pro-vue-ui-kit";
import { CometChatConversationListWithMessages } from "../cometchat-pro-vue-ui-kit/CometChatWorkspace/src";
export default {
    name: "Inbox",
    components: {
        CometChatConversationListWithMessages
    }
}
</script>

<style scoped>
#inbox-wrapper {
    width: 100%;
    /* position: absolute; */
    height: 100%;
    min-height: 500px;
}
</style>
