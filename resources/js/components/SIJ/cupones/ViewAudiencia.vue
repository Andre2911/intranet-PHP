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
            <v-row>
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
                        </v-list-item-group>
                    </v-list>
                </v-col>
                <v-col cols="12" md="4">
                    <v-list subheader dense>
                        <v-list-item-group>
                            <v-subheader>ESTADO DE LA AUDIENCIA</v-subheader>
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
                            <v-list-item @click="(activeItem.RUTA_FTP_ARCHIVO != null)? getFile(activeItem.DEN_ARCHIVO_ACTA):''">
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.RUTA_FTP_ARCHIVO}}{{activeItem.DEN_ARCHIVO_ACTA}}</v-list-item-title>
                                    <v-list-item-subtitle>Acta</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            <v-divider class="mt-0 mb-0"></v-divider>
                            <v-list-item @click="(activeItem.DEN_ARCHIVO_AUDIO_VIDEO != null)? getFile(activeItem.DEN_ARCHIVO_AUDIO_VIDEO):''">
                                <v-list-item-content>
                                    <v-list-item-title>{{activeItem.DEN_ARCHIVO_AUDIO_VIDEO}}</v-list-item-title>
                                    <v-list-item-subtitle>Audio / video</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            
                        </v-list-item-group>
                    </v-list>
                </v-col>
            </v-row>
        </v-card-text>
        
    </v-card>
</template>
<script>
export default {
    props:['activeItem'],
    methods: {
        getFile(filename){
            let url = '../consultasSIJ/getAudActa?ruta=' + this.activeItem.RUTA_FTP_ARCHIVO + 
                                                                                    '&filename=' + filename + 
                                                                                    '&destino=\\172.17.176.17\resoluciones$' + 
                                                                                    '&ftpUsername=' + this.activeItem.IP_FTP_USUARIO +
                                                                                    '&ftpPassword=' + this.activeItem.IP_FTP_CLAVE + 
                                                                                    '&ftpIP=' + this.activeItem.IP_FTP_ARCHIVO;

            axios.get(url)
                .then(response =>{
                    if(response.data['Status'] != 0){
                        console.log(response.data)
                        window.open(
                            "http://172.17.176.17/intranet/storage/app/resoluciones/"+(this.activeItem.RUTA_FTP_ARCHIVO).substring(0,12)+'/'+filename, "_blank");
                    } else{
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
                    if (errors.message === "Network Error"){}
                    if (errors.response.status === 500){}
                });
        }
    },
}
</script>