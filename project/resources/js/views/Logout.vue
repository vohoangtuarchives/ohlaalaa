<template>
    <div>
        <a href="#" v-on:click="logout"><i class="fas fa-angle-double-right"></i> {{ this.lang  }}</a>
    </div>
</template>

<script>
import { CometChat } from "@cometchat-pro/chat";

    export default {
        props: ['url', 'lang'],
        data() {
            return {
            };
        },
        mounted() {

        },
        methods: {
            logout() {
                CometChat.logout().then(
                    () => {
                        window.location.href = this.url;
                    },error=>{
                        console.log("Logout failed with exception:",{error});
                        if(error.code == 'USER_NOT_LOGED_IN')
                            window.location.href = this.url;
                    }
                );
            },
        }
    };
</script>
