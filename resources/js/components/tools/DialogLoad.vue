<template>
    <v-dialog v-model="dialogLoad.show" persistent :width="290" transition="dialog-transition">
        <v-card dark :color="dialogLoad.color">
            <v-card-text>
                {{dialogLoad.text}}
                <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>
<script>
export default {
    props: ['dialogLoad'],
    methods: {
        
    },
}
</script>