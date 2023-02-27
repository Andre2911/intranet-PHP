<template>
    <v-app-bar app color="red darken-4" dark 
        scroll-target="#contentApp">
        <a v-if="modulo != 'dashboard'" :href="(modulo != 'dashboard')? '../inicio':'#'"> <v-icon>mdi-arrow-left-circle</v-icon> </a>
        <v-toolbar-title row wrap>
            <v-layout wrap>
                &nbsp;
                <v-flex xs1 mr-4>
                    <v-img :src="(modulo != 'dashboard')? '../public/image/logo.png':'public/image/logo.png'" width="40px"    max-width="40"
                height="40px"/>
                </v-flex>
                <v-flex xs9>
                    <template v-if="drawer">
                        &nbsp;&nbsp;Corte Superior de Justicia de Arequipa
                    </template>
                    <template v-else>
                        &nbsp;&nbsp;&nbsp;&nbsp;<b>CSJ de Arequipa</b>
                    </template>
                </v-flex>
            </v-layout>
            
        </v-toolbar-title>

        <v-spacer />


        <v-app-bar-nav-icon @click.stop="cDrawer" />
    </v-app-bar>
</template>
<script>
export default {
    props: ['drawer', 'modulo'],
    data: () => ({
    	sticky: false,
    }),
    methods: {
    	cDrawer(){
            this.$emit('cDrawer');
        },

    }
}
</script>