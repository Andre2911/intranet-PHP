<template>
    <v-snackbar v-model="snack.snackShow" top right :timeout="6000"  :color="snack.snackColor">
        {{ snack.snackText }}
        <template v-slot:action="{ attrs }">
        <v-btn text @click="snack.snackShow = false" v-bind="attrs"><v-icon>mdi-close</v-icon></v-btn>
        </template>
    </v-snackbar>
</template>
<script>
export default {
    props: ['snack'],
    methods: {
        
    },
}
</script>