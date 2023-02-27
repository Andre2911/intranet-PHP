<template>
    <v-app id="inspire">
        		<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

        <menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser" />
        <v-main id="contentApp">
            <v-row>
                <v-col md="8" xs="12" sm="12">
                    <v-container>
                        <v-card>
                            <v-card-title>
                                Información de Usuario
                            </v-card-title>
                            <v-card-text>
                                <v-list two-line subheader dense>
                                    <v-subheader>Datos Generales</v-subheader>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.numero_documento}}</v-list-item-title>
                                            <v-list-item-subtitle>Documento</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.ap_paterno}} {{dataUser.usuario.ap_materno}}</v-list-item-title>
                                            <v-list-item-subtitle>Apellidos</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.nombres}}</v-list-item-title>
                                            <v-list-item-subtitle>Nombres</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-subheader>Datos Laborales</v-subheader>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.email}}</v-list-item-title>
                                            <v-list-item-subtitle>Correo electrónico</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.c_plaza_titular}}-{{dataUser.usuario.n_plaza_titular}}</v-list-item-title>
                                            <v-list-item-subtitle>Plaza Titular</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.n_oficina_titular}}</v-list-item-title>
                                            <v-list-item-subtitle>Ubicación</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>
                                    <v-list-item>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.c_plaza_fisica}}-{{dataUser.usuario.n_plaza_fisica}}</v-list-item-title>
                                            <v-list-item-subtitle>Plaza Física</v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-content>
                                            <v-list-item-title>{{dataUser.usuario.n_oficina_fisica}}</v-list-item-title>
                                            <v-list-item-subtitle>Ubicación</v-list-item-subtitle>
                                        </v-list-item-content>
                                    </v-list-item>

                                </v-list>
                            </v-card-text>
                        </v-card>
                    </v-container>
                </v-col>
                <v-col md="4" sm="12" xs="12">
                    <v-container>
                        <v-card>
                            <v-card-title>
                                Cambiar contraseña
                            </v-card-title>
                            <v-card-text>
                                <ValidationObserver ref="observer" v-slot="{ }">
                                    <v-form ref="form"
                                        v-model="valid"
                                        :lazy-validation="lazy"
                                    >
                                        <v-col>
                                            <v-text-field
                                                v-model="current_password"
                                                label="Antigua contraseña"
                                                type="password"
                                                :rules="rule_req"
                                                required
                                            />
                                            <ValidationProvider rules="password:@confirm" v-slot="{ errors }">
                                                <v-text-field
                                                    v-model="new_password"
                                                    label="Nueva contraseña"
                                                    type="password"
                                                    ref="new_password"
                                                    :rules="rule_req"
                                                    :error-messages="errors"
                                                    required
                                                />
                                            </ValidationProvider>
                                            <ValidationProvider name="confirm" v-slot="{ errors }">
                                                <v-text-field
                                                    v-model="repeat_password"
                                                    label="Repetir contraseña"
                                                    type="password"
                                                    :rules="rule_req"
                                                    required
                                                    :error-messages="errors"
                                                />
                                            </ValidationProvider>

                                            <v-btn color="success" block large :disabled="!valid" @click="changePassword()">
                                                <v-icon>
                                                    mdi-content-save
                                                </v-icon>
                                                Cambiar contraseña
                                            </v-btn>
                                        </v-col>
                                    </v-form>
                                </ValidationObserver>
                            </v-card-text>
                        </v-card>
                    </v-container>
                </v-col>

                <v-col cols="12" md="12" sm="12" xs="12" v-if="dataUser.usuario.c_regimen == 2">
                    <usuario-pprofesional @showSnack = "showSnack" @showLoad = "showLoad"/>
                </v-col>
            </v-row>

            <snackbar :snack="snack" />
            <dialogLoader :dialogLoad="dialogLoad"/>
        </v-main>
        <footer-component/>
    </v-app>
</template>
<script>
import { required, confirmed, max } from 'vee-validate/dist/rules'
import { extend, ValidationObserver, ValidationProvider, setInteractionMode } from 'vee-validate'

setInteractionMode('eager')

  extend('required', {
    ...required,
    message: 'El campo no puede estar vacio',
  })

  extend('password', {
        params: ['target'],
        validate(value, { target }) {
            return target.length < 6 || (value === target && value.length >= 6);
        },
        message: 'Las contraseñas no coinciden'
    });

export default {
    components: {
      ValidationProvider,
      ValidationObserver,
    },
    props:['data-user'],
    data() {
        return {
            valid: true,
            lazy: false,
            menu: 5,
            submenu: 0,
            modulo: 'perfil',
            drawer:true,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            current_password:'',
            new_password:'',
            repeat_password:'',
            rule_req: [
                v => !!v || 'Campo requerido',
                v => (v && v.length >= 6) || '6 caracteres como mínimo',
            ],
            dialogLoad:{
                show:false,
                text:'',
                color:'',
            }
        }
    },
    created() {
    },
    methods:{
        
        changePassword(){
            this.$refs.observer.validate()
            
            if(this.$refs.form.validate()){
                if(this.new_password == this.repeat_password){
                    let formData = new FormData();
                    formData.append('old_password', this.current_password);
                    formData.append('new_password', this.new_password);
                    
                    axios.post('changePassword', formData)
                        .then(response =>{
                            if (response.data['statusBD']) {
                                this.showSnack('success', response.data['messageDB'])
                                this.$refs.form.reset()
                            } else{
                                this.showSnack('red', response.data['messageDB'])
                            }
                        })
                        .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status === 401) {
                                console.log(errors.response)
                                if(errors.response.data.error == 'Unauthenticated'){
                                    window.location = "../login";
                                }
                            }
                        });
                } 
            }
        },
        cDrawer(val){
            if(val != undefined ){
                this.drawer = val;
            } else{
                this.drawer = !(this.drawer);
            }
        },
        showSnack(color, text, show = true){
            this.snack.snackColor = color
            this.snack.snackText = text
            this.snack.snackShow = show
        },
        showLoad(color, text, show = true){
            this.dialogLoad.color = color
            this.dialogLoad.text = text
            this.dialogLoad.show = show
        },
    }

}
</script>