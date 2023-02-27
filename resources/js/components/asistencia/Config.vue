<template>
	<v-app id="inspire">
        <header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>
		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
		<v-main id="contentApp">
			<v-card>
				<v-app-bar dark color="grey">
                    <v-toolbar-title>Configuración de asistencias</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn color="info" @click="listarData()">
                        <v-icon>mdi-refresh</v-icon>
                                Refrescar
                    </v-btn>
                    <v-btn color="success" @click="save()">
                        <v-icon>mdi-content-save</v-icon>
                                Guardar cambios
                    </v-btn>
                </v-app-bar>
                <v-card-text>
                    <v-container>
                        <v-row v-if="configuracion != null">
                            <!--<v-col cols="12" xs="12" sm="4" md="4">
                                <v-subheader class="text-primary">PRESENCIAL</v-subheader>
                                <v-row>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_ing_presencial"
                                            v-model="h_ing_presencial"
                                            :return-value.sync="time_ing_presencial"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_ingreso_presencial"
                                                    label="H. Ingreso Presencial"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_ing_presencial"
                                                v-model="time_ing_presencial"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_ing_presencial = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_ing_presencial.save(time_ing_presencial)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_d_ing_presencial"
                                            v-model="h_d_ing_presencial"
                                            :return-value.sync="time_d_ing_presencial"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_disp_ing_presencial"
                                                    label="H. Disp. Ingreso Presencial"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_d_ing_presencial"
                                                v-model="time_d_ing_presencial"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_d_ing_presencial = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_d_ing_presencial.save(time_d_ing_presencial)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_ini_refrigerio"
                                            v-model="h_ini_refrigerio"
                                            :return-value.sync="time_ini_refrigerio"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_ini_refrigerio"
                                                    label="H. Inicio Refrigerio"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_ini_refrigerio"
                                                v-model="time_ini_refrigerio"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_ini_refrigerio = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_ini_refrigerio.save(time_ini_refrigerio)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_d_ini_refrigerio"
                                            v-model="h_d_ini_refrigerio"
                                            :return-value.sync="time_d_ini_refrigerio"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_disp_ini_refrigerio"
                                                    label="H. Disp. Inicio Refrigerio"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                    color="primary"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_d_ini_refrigerio"
                                                v-model="time_d_ini_refrigerio"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_d_ini_refrigerio = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_d_ini_refrigerio.save(time_d_ini_refrigerio)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_fin_refrigerio"
                                            v-model="h_fin_refrigerio"
                                            :return-value.sync="time_fin_refrigerio"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_fin_refrigerio"
                                                    label="H. Retorno Refrigerio"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                    color="primary"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_fin_refrigerio"
                                                v-model="time_fin_refrigerio"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_fin_refrigerio = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_fin_refrigerio.save(time_fin_refrigerio)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_d_fin_refrigerio"
                                            v-model="h_d_fin_refrigerio"
                                            :return-value.sync="time_d_fin_refrigerio"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_disp_fin_refrigerio"
                                                    label="H. Disp. Retorno Refrigerio"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                    color="primary"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_d_fin_refrigerio"
                                                v-model="time_d_fin_refrigerio"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_d_fin_refrigerio = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_d_fin_refrigerio.save(time_d_fin_refrigerio)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_fin_presencial"
                                            v-model="h_fin_presencial"
                                            :return-value.sync="time_fin_presencial"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_salida_presencial"
                                                    label="H. Salida Presencial"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                    color="primary"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_fin_presencial"
                                                v-model="time_fin_presencial"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_fin_presencial = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_fin_presencial.save(time_fin_presencial)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_d_fin_presencial"
                                            v-model="h_d_fin_presencial"
                                            :return-value.sync="time_d_fin_presencial"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_disp_salida_presencial"
                                                    label="H. Disp. Salida Presencial"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                    color="primary"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_d_fin_presencial"
                                                v-model="time_d_fin_presencial"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_d_fin_presencial = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_d_fin_presencial.save(time_d_fin_presencial)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-subheader>AUTORIZACIÓN DE INGRESO</v-subheader>
                                        <v-dialog
                                            ref="h_aut_ingreso"
                                            v-model="h_aut_ingreso"
                                            :return-value.sync="time_aut_ingreso"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_aut_ingreso"
                                                    label="H. Autorización de ingreso"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_aut_ingreso"
                                                v-model="time_aut_ingreso"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_aut_ingreso = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_aut_ingreso.save(time_aut_ingreso)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-subheader>HORAS LABORADAS DIARIAS</v-subheader>
                                        <v-text-field
                                            v-model="configuracion.max_horas_diarias"
                                            label="Max. horas laborables"
                                            slot="activator"
                                            color="red"
                                            type="number"
                                            :rules="[rules.required]"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>

                            </v-col>

                            <v-col cols="12" xs="12" sm="4" md="4">
                                <v-subheader class="text-info">EXCEPCIONAL</v-subheader>
                                <v-row>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_ing_excepcional"
                                            v-model="h_ing_excepcional"
                                            :return-value.sync="time_ing_excepcional"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_ingreso_excepcional"
                                                    label="H. Ingreso Excepcional"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_ing_excepcional"
                                                v-model="time_ing_excepcional"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_ing_excepcional = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_ing_excepcional.save(time_ing_excepcional)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_d_ing_pres_excep"
                                            v-model="h_d_ing_pres_excep"
                                            :return-value.sync="time_d_ing_pres_excep"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_disp_ing_excepcional"
                                                    label="H. Disp. Ingreso Excepcional"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_d_ing_pres_excep"
                                                v-model="time_d_ing_pres_excep"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_d_ing_pres_excep = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_d_ing_pres_excep.save(time_d_ing_pres_excep)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>

                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_fin_pres_excep"
                                            v-model="h_fin_pres_excep"
                                            :return-value.sync="time_fin_pres_excep"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_salida_excepcional"
                                                    label="H. Salida Excepcional"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_fin_pres_excep"
                                                v-model="time_fin_pres_excep"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_fin_pres_excep = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_fin_pres_excep.save(time_fin_pres_excep)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>

                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_d_fin_pres_excep"
                                            v-model="h_d_fin_pres_excep"
                                            :return-value.sync="time_d_fin_pres_excep"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_disp_salida_excepcional"
                                                    label="H. Disp. Salida Excepcional"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_d_fin_pres_excep"
                                                v-model="time_d_fin_pres_excep"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_d_fin_pres_excep = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_d_fin_pres_excep.save(time_d_fin_pres_excep)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-subheader>AUTORIZACIÓN DE INGRESO EXCEPCIONAL</v-subheader>
                                        <v-dialog
                                            ref="h_aut_ingreso_exc"
                                            v-model="h_aut_ingreso_exc"
                                            :return-value.sync="time_aut_ingreso_exc"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_aut_ingreso_excepcional"
                                                    label="H. Autorización de ingreso Excepcional"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_aut_ingreso_exc"
                                                v-model="time_aut_ingreso_exc"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_aut_ingreso_exc = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_aut_ingreso_exc.save(time_aut_ingreso_exc)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-subheader>HORAS LABORADAS DIARIAS EXCEPCIONAL</v-subheader>
                                        <v-text-field
                                            v-model="configuracion.max_horas_excepcional"
                                            label="Max. horas laborables"
                                            slot="activator"
                                            color="red"
                                            type="number"
                                            :rules="[rules.required]"
                                        ></v-text-field>
                                    </v-col>

                                </v-row>
                            </v-col>
-->
                            <v-col cols="12" xs="12" sm="4" md="4">
                                <v-subheader class="text-info">REMOTO</v-subheader>
                                <v-row>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_ing_remoto"
                                            v-model="h_ing_remoto"
                                            :return-value.sync="time_ing_remoto"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_ingreso_remoto"
                                                    label="H. Ingreso Remoto"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_ing_remoto"
                                                v-model="time_ing_remoto"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_ing_remoto = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_ing_remoto.save(time_ing_remoto)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_d_ing_remoto"
                                            v-model="h_d_ing_remoto"
                                            :return-value.sync="time_d_ing_remoto"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_disp_ing_remoto"
                                                    label="H. Disp. Ingreso Remoto"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_d_ing_remoto"
                                                v-model="time_d_ing_remoto"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_d_ing_remoto = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_d_ing_remoto.save(time_d_ing_remoto)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_fin_remoto"
                                            v-model="h_fin_remoto"
                                            :return-value.sync="time_fin_remoto"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_salida_remoto"
                                                    label="H. Salida Remoto"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                    color="primary"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_fin_remoto"
                                                v-model="time_fin_remoto"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_fin_remoto = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_fin_remoto.save(time_fin_remoto)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>
                                    <v-col cols="12" xs="12" sm="6" md="6">
                                        <v-dialog
                                            ref="h_d_fin_remoto"
                                            v-model="h_d_fin_remoto"
                                            :return-value.sync="time_d_fin_remoto"
                                            persistent
                                            width="290px"
                                        >
                                            <template v-slot:activator="{ on }">
                                                <v-text-field
                                                    v-model="configuracion.h_disp_salida_remoto"
                                                    label="H. Disp. Salida Remoto"
                                                    slot="activator"
                                                    :rules="[rules.required]"
                                                    v-on="on"
                                                    color="primary"
                                                ></v-text-field>
                                            </template>
                                            <v-time-picker
                                                v-if="h_d_fin_remoto"
                                                v-model="time_d_fin_remoto"
                                                full-width use-seconds
                                            >
                                                <v-spacer></v-spacer>
                                                <v-btn text color="primary" @click="h_d_fin_remoto = false">Cancel</v-btn>
                                                <v-btn text color="primary" @click="$refs.h_d_fin_remoto.save(time_d_fin_remoto)">OK</v-btn>
                                            </v-time-picker>
                                        </v-dialog>
                                    </v-col>

                                </v-row>

                            </v-col>

                            

                        </v-row>
                    </v-container>
                    
                </v-card-text>
            </v-card>
        </v-main>
        <snackbar :snack="snack" ></snackbar>
        <footer-component/>
    </v-app>
</template>


<script>
export default {
    props:['dataUser'],
    data() {
        return {
            menu: this.hasRole('Asistencia.administrador')? 4: 6,
            submenu: -1,
            modulo: 'asistencia',
            drawer:true,
            snack: {
                snackShow:false,
                snackText: '',
                snackColor: '',
            },
            rules: {
                entero: v => v > 0 || 'El numero debe ser mayor a 0',
                required: v => !!v || 'Descripcion Requerido',
                notNUll: v => v != null || 'Indicar la observación de la modificación y/o regularización',
                length_250: v => (v != null && v.length <= 250) || 'Max 250 caracteres'
            },
            configuracion:null,
            h_ing_presencial: false,
            time_ing_presencial: null,
            h_d_ing_presencial: false,
            time_d_ing_presencial: null,
            h_ini_refrigerio: false,
            time_ini_refrigerio: null,
            h_d_ini_refrigerio: false,
            time_d_ini_refrigerio: null,
            h_fin_refrigerio: false,
            time_fin_refrigerio: null,
            h_d_fin_refrigerio: false,
            time_d_fin_refrigerio: null,
            h_fin_presencial: false,
            time_fin_presencial: null,
            h_d_fin_presencial: false,
            time_d_fin_presencial: null,

            h_ing_excepcional: false,
            time_ing_excepcional: null,
            h_d_ing_pres_excep: false,
            time_d_ing_pres_excep: null,
            h_fin_pres_excep: false,
            time_fin_pres_excep: null,
            h_d_fin_pres_excep: false,
            time_d_fin_pres_excep: null,

            h_ing_remoto: false,
            time_ing_remoto: null,
            h_d_ing_remoto: false,
            time_d_ing_remoto: null,
            h_fin_remoto: false,
            time_fin_remoto: null,
            h_d_fin_remoto: false,
            time_d_fin_remoto: null,

            h_aut_ingreso: false,
            time_aut_ingreso: null,
            h_aut_ingreso_exc: false,
            time_aut_ingreso_exc: null,
        }
    },
    watch: {
        time_ing_presencial (val) {
            if(this.configuracion != null){
                this.configuracion.h_ingreso_presencial = this.time_ing_presencial
            }
        },
        time_d_ing_presencial (val) {
            if(this.configuracion != null){
                this.configuracion.h_disp_ing_presencial = this.time_d_ing_presencial
            }
        },
        time_ing_excepcional (val) {
            if(this.configuracion != null){
                this.configuracion.h_ingreso_excepcional = this.time_ing_excepcional
            }
        },
        time_d_ing_pres_excep (val) {
            if(this.configuracion != null){
                this.configuracion.h_disp_ing_excepcional = this.time_d_ing_pres_excep
            }
        },
        time_ini_refrigerio (val) {
            if(this.configuracion != null){
                this.configuracion.h_ini_refrigerio = this.time_ini_refrigerio
            }
        },
        time_d_ini_refrigerio (val) {
            if(this.configuracion != null){
                this.configuracion.h_d_ini_refrigerio = this.time_d_ini_refrigerio
            }
        },
        time_fin_refrigerio (val) {
            if(this.configuracion != null){
                this.configuracion.h_fin_refrigerio = this.time_fin_refrigerio
            }
        },
        time_d_fin_refrigerio (val) {
            if(this.configuracion != null){
                this.configuracion.h_disp_fin_refrigerio = this.time_d_fin_refrigerio
            }
        },
        time_fin_presencial (val) {
            if(this.configuracion != null){
                this.configuracion.h_salida_presencial = this.time_fin_presencial
            }
        },
        time_d_fin_presencial (val) {
            if(this.configuracion != null){
                this.configuracion.h_disp_salida_presencial = this.time_d_fin_presencial
            }
        },
        time_fin_pres_excep (val) {
            if(this.configuracion != null){
                this.configuracion.h_salida_excepcional = this.time_fin_pres_excep
            }
        },
        time_d_fin_pres_excep (val) {
            if(this.configuracion != null){
                this.configuracion.h_disp_salida_excepcional = this.time_d_fin_pres_excep
            }
        },
        

        time_ing_remoto (val) {
            if(this.configuracion != null){
                this.configuracion.h_ingreso_remoto = this.time_ing_remoto
            }
        },
        time_d_ing_remoto (val) {
            if(this.configuracion != null){
                this.configuracion.h_disp_ing_remoto = this.time_d_ing_remoto
            }
        },
        time_fin_remoto (val) {
            if(this.configuracion != null){
                this.configuracion.h_salida_remoto = this.time_fin_remoto
            }
        },
        time_d_fin_remoto (val) {
            if(this.configuracion != null){
                this.configuracion.h_disp_salida_remoto = this.time_d_fin_remoto
            }
        },

        time_aut_ingreso (val) {
            if(this.configuracion != null){
                this.configuracion.h_aut_ingreso = this.time_aut_ingreso
            }
        },
        time_aut_ingreso_exc (val) {
            if(this.configuracion != null){
                this.configuracion.h_aut_ingreso_excepcional = this.time_aut_ingreso_exc
            }
        },
    }, 
    created() {
        this.listarData();
    },
    methods: {
        listarData(pag=1, search=null, perpage=15){
            this.itemsPerPage = perpage;
            var url = 'getConfiguracion';
            this.pagina_actual = pag;

            axios.get(url)
                .then(response =>{
                    this.configuracion = response.data.configuracion;
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.response.status === 401) {
                        console.log(errors.response)
                        if(errors.response.data.error == 'Unauthenticated'){
                            window.location = "../login";
                        }
                    } else if (errors.response.status === 403) {
                        console.log(errors.response)
                            window.location = "../inicio";
                    } else{
                        //this.listarData();
                    }
                    });
        },
        save(){
            axios.post('setConfiguracion', this.configuracion)
                        .then(response =>{
                                if (response.data['statusBD']) {
                                    this.listarData();
                                    this.showSnack('success', response.data['messageDB'])
                                } else{
                                    this.showSnack('error', response.data['messageDB'])
                                }
                        })
                        .catch(errors =>{
                            console.log(errors);
                            if (errors.response != undefined && errors.response.status === 401) {
                                console.log(errors.response)
                                if(errors.response.data.error == 'Unauthenticated'){
                                    window.location = "inicio";
                                }
                            }
                        });
        },
        formatDate (date) {
            if (!date) return null
            const [year, month, day] = date.split('-')
            return `${day}/${month}/${year}`
        },
        parseDate (date) {
            if (!date) return null
            const [day, month, year] = date.split('/')
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
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
        hasRole(role){
            var roles = role.split('|');
            for (let index = 0; index < roles.length; index++) {
                if (this.dataUser.modulos.includes(roles[index])) {
                    return this.dataUser.modulos.includes(roles[index]);
                }
            } 
            return false;
        },
    }
}
</script>