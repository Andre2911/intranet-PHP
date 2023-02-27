<template>
    <v-card>
        <v-toolbar color="red darken-4" dark>
            <v-toolbar-title>
                <b>Expediente: {{activeItem.NUM_EXPEDIENTE}}</b>
            </v-toolbar-title>
            <v-spacer/>
            <v-btn  icon  dark @click="$emit('close')">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-toolbar>
        <v-card-text>
            <v-row class="mt-1">
                <v-col cols="12" md="4">
                    <v-list subheader dense>
                        <v-list-item-group>
                            <v-subheader>DATOS GENERALES</v-subheader>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.NUM_EXPEDIENTE}}</v-list-item-title>
                                    <v-list-item-subtitle>Expediente</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_ESPECIALIDAD}}</v-list-item-title>
                                    <v-list-item-subtitle>Especialidad</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_INCIDENTE}}</v-list-item-title>
                                    <v-list-item-subtitle>Cuaderno</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.NOM_USUARIO_SECRETARIO}}</v-list-item-title>
                                    <v-list-item-subtitle>Secretario(a)</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_INSTANCIA_SIJ}}</v-list-item-title>
                                    <v-list-item-subtitle>Instancia</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_DEPENDENCIA}}</v-list-item-title>
                                    <v-list-item-subtitle>Dependencia</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_ORG_JURISDICCIONAL}}</v-list-item-title>
                                    <v-list-item-subtitle>Organo Jurisdiccional</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_SEDE_JUDICIAL}}</v-list-item-title>
                                    <v-list-item-subtitle>Sede</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list-item-group>
                        
                        
                    </v-list>
                </v-col>
                <v-col cols="12" md="4">
                     <v-list subheader dense>
                        <v-list-item-group>
                            <v-subheader>DATOS AUDIENCIA</v-subheader>
                            <template  v-if="activeItem.COD_RESERVA_AGENDA != 'S'">
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.DEN_ACTIVIDAD}}</v-list-item-title>
                                        <v-list-item-subtitle>Actividad</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.OBS_ACTIVIDAD}}</v-list-item-title>
                                        <v-list-item-subtitle>Obs. Actividad</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.FECHA_INI_ACTIVIDAD}}</v-list-item-title>
                                        <v-list-item-subtitle>Inicio</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.FECHA_FIN_ACTIVIDAD}}</v-list-item-title>
                                        <v-list-item-subtitle>Final</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.DURACION_EN_MINUTOS}}</v-list-item-title>
                                        <v-list-item-subtitle>Duración (minutos)</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.NOM_USUARIO_AGENDA}}</v-list-item-title>
                                        <v-list-item-subtitle>Usuario de Agenda</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.FECHA_AGENDA}}</v-list-item-title>
                                        <v-list-item-subtitle>Fecha de Agenda</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.DEN_SALA_AUDIENCIA}} / {{activeItem.DEN_SEDE_EP_AUDIENCIA}} </v-list-item-title>
                                        <v-list-item-subtitle>Lugar de audiencia</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </template>
                            <template v-else>
                                <v-alert
                                    color="warning"
                                    border="left"
                                    elevation="2"
                                    colored-border
                                    icon="mdi-alert"
                                    >
                                    {{activeItem.DEN_RESERVA_AGENDA}}
                                </v-alert>
                            </template>
                        </v-list-item-group>
                    </v-list>
                </v-col>
                <v-col cols="12" md="4">
                    <v-list subheader dense>
                        <v-list-item-group>
                            <v-subheader>ESTADO DE LA AUDIENCIA</v-subheader>
                            <template  v-if="activeItem.COD_RESERVA_AGENDA != 'S'">

                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title><b>{{activeItem.DEN_ESTADO_AUDIENCIA}}</b></v-list-item-title>
                                        <v-list-item-subtitle>Estado</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.DEN_ESTADO_ACTIVIDAD}}</v-list-item-title>
                                        <v-list-item-subtitle>Estado actividad</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.SUMILLA_AUDIENCIA}}</v-list-item-title>
                                        <v-list-item-subtitle>Sumilla Audiencia</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.DEN_AUDIENCIA}}</v-list-item-title>
                                        <v-list-item-subtitle>Denominación Audiencia</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item>
                                    <v-list-item-content>
                                        <v-list-item-title>{{activeItem.NOM_USUARIO_AUDIO}}</v-list-item-title>
                                        <v-list-item-subtitle>Usuario audio</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                
                                <v-list-item @click="(activeItem.RUTA_FTP_ARCHIVO != null && activeItem.DEN_ARCHIVO_ACTA != null)? getFile(activeItem.DEN_ARCHIVO_ACTA):''">
                                    <v-list-item-content>
                                        <v-btn color="info" outlined small v-if="activeItem.RUTA_FTP_ARCHIVO != null && activeItem.DEN_ARCHIVO_ACTA != null">
                                            <v-icon>mdi-file-word</v-icon>
                                            DESCARGAR ACTA
                                        </v-btn>
                                        <v-list-item-subtitle>Acta</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider class="mt-0 mb-0"></v-divider>
                                <v-list-item @click="(activeItem.DEN_ARCHIVO_AUDIO_VIDEO != nul)? getFile(activeItem.DEN_ARCHIVO_AUDIO_VIDEO):''">
                                    <v-list-item-content>
                                        <v-btn color="warning" outlined small v-if="activeItem.DEN_ARCHIVO_AUDIO_VIDEO != null" >
                                            <v-icon>mdi-file-music-outline</v-icon>
                                            OBTENER AUDIO / VIDEO
                                        </v-btn>
                                        <v-list-item-subtitle>Audio / video</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </template>
                            <template v-else>
                                <v-alert
                                    color="warning"
                                    border="left"
                                    elevation="2"
                                    colored-border
                                    icon="mdi-alert"
                                    >
                                    {{activeItem.DEN_RESERVA_AGENDA}}
                                </v-alert>
                            </template>
                        </v-list-item-group>
                    </v-list>
                </v-col>
            </v-row>
        </v-card-text>
        
    </v-card>
</template>
<script>
export default {
    props:['activeItem', 'dataUser'],
    methods: {
        getFile(filename){
            this.$emit('showLoad', 'primary', 'Obteniendo archivo')


            let url = '../consultasSIJ/getAudActa?ruta=' + this.activeItem.RUTA_FTP_ARCHIVO + 
                                                                                    '&filename=' + filename + 
                                                                                    '&destino=\\172.17.176.17\resoluciones$' + 
                                                                                    '&ftpUsername=' + this.activeItem.IP_FTP_USUARIO +
                                                                                    '&ftpPassword=' + this.activeItem.IP_FTP_CLAVE + 
                                                                                    '&ftpIP=' + this.activeItem.IP_FTP_ARCHIVO +
                                                                                    '&filename_destino=' + this.dataUser.usuario.numero_documento
                                                                                    ;

            axios.get(url)
                .then(response =>{
                    console.log(response.data)

                    if(response.data['status'] != 0 && response.data['data']['Status'] != 0){
                        window.open(    
                            "http://172.17.176.17/intranet/storage/app/resoluciones/tmp/"+response.data['data']['FilenameD'], "_blank");
                    } else{
                        this.$emit('showSnack', 'warning', 'No se pudo realizar la descarga, comuniquese con el administrador')
                    }
                    this.$emit('showLoad', '', '', false)

                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                             window.location = "../login";
                        }
                    }
                    if (errors.message === "Network Error"){}
                    if (errors.response.status === 500){}
                });
        }
    },
}
</script>