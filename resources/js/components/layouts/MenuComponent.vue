<template>
    <v-navigation-drawer v-model="mdrawer" app right light>
        <template v-slot:prepend>   
        <v-img
            :src="(modulo == 'dashboard')? 'public/image/fachadacorte.jpg': (modulo == 'permisos_sede' || modulo == 'repolaboral')? '../../public/image/fachadacorte.jpg':'../public/image/fachadacorte.jpg'"
            gradient="to bottom, rgba(0,0,0,.4), rgba(0,0,0,.6)"
            height="150px"
            dark
        >
            <v-list-item two-line :href="(modulo != 'dashboard')? '../inicio':'#'" src="../public/image/sideminibg.png">
                <v-list-item-avatar>
                    <v-icon color="red darken-3">mdi-arrange-send-to-back</v-icon>
                </v-list-item-avatar>

            <v-list-item-content>
                <v-list-item-title>INTRANET</v-list-item-title>
                <v-list-item-subtitle>CSJ Arequipa</v-list-item-subtitle>
            </v-list-item-content>
            </v-list-item>
            <v-list-item link>
                    <v-list-item-content>
                        <v-list-item-title  class="title">{{ dataUser.usuario.nombres }}</v-list-item-title>
                        <v-list-item-subtitle  class="title">{{ dataUser.usuario.ap_paterno }} {{ dataUser.usuario.ap_materno }}</v-list-item-subtitle>
                        <!--<v-list-item-subtitle>{{ email }}</v-list-item-subtitle>-->
                    </v-list-item-content>

                    <v-list-item-action>
                        <v-icon>mdi-account</v-icon>
                    </v-list-item-action>
                </v-list-item>

                <template v-slot:placeholder>
                    <v-row
                        class="fill-height ma-0"
                        align="center"
                        justify="center"
                    >
                        <v-progress-circular
                        indeterminate
                        color="grey lighten-5"
                        ></v-progress-circular>
                    </v-row>
                </template>
        </v-img>
        </template>
        <v-list nav rounded>
            <v-list-item v-if="modulo != 'dashboard'" link :href="(modulo == 'permisos_sede')? '../../dashboard':'../dashboard'">
                <v-list-item-action>
                    <v-icon>mdi-monitor-dashboard</v-icon>
                </v-list-item-action>

                <v-list-item-content>
                    <v-list-item-title>MENU PRINCIPAL </v-list-item-title>
                </v-list-item-content>
            </v-list-item>
            <v-list-item-group v-model='menu' color="red darken-4" dense>
                <template v-if="modulo == 'usuario'" >
                    <v-list-item href="dashboard">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-view-dashboard</v-icon>
                        </v-list-item-avatar>

                        <v-list-item-content>
                            <v-list-item-title>Inicio</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-list-item href="registro">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-upload</v-icon>
                        </v-list-item-avatar>

                        <v-list-item-content>
                            <v-list-item-title>Registrar Ingreso</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-list-item link href="seguimiento">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white'>mdi-file-search</v-icon>
                        </v-list-item-avatar>

                        <v-list-item-content>
                            <v-list-item-title>Seguimiento</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </template>


                <template v-else-if="modulo == 'admin'">
    <!-------------------------------------------------------------------------

    --------------- / MENU DE MÓDULO DE MANTENIMIENTO /------------------------------

    -------------------------------------------------------------------------->
                    <v-list-item href="inicio">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-view-dashboard</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Inicio</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-group
                        :value="menu == 1"
                        no-action
                    >
                        <template v-slot:activator>
                            <v-list-item-avatar>
                                <v-icon class='red darken-4 text-white' >mdi-tune-vertical</v-icon>
                            </v-list-item-avatar>
                            <v-list-item-title>Mantenimiento </v-list-item-title>
                        </template>
                        <v-list-item  v-if="hasRole('Administrador')" :class="submenu == 0? 'v-item--active v-list-item--active':''"  link href="usuarios">
                            <v-list-item-title> Usuarios</v-list-item-title>
                            <v-list-item-icon>
                                <v-icon>mdi-account</v-icon>
                            </v-list-item-icon>
                        </v-list-item>
                        <v-list-item v-if="hasRole('Webmaster')" :class="submenu == 1? 'v-item--active v-list-item--active':''"  link href="roles">
                            <v-list-item-title> Roles</v-list-item-title>
                            <v-list-item-icon>
                                <v-icon>mdi-table-account</v-icon>
                            </v-list-item-icon>
                        </v-list-item>
                        <v-list-item :class="submenu == 2? 'v-item--active v-list-item--active':''"  link href="personas">
                            <v-list-item-title> Personas</v-list-item-title>
                            <v-list-item-icon>
                                <v-icon>mdi-account-group</v-icon>
                            </v-list-item-icon>
                        </v-list-item>
                        <v-list-item :class="submenu == 3? 'v-item--active v-list-item--active':''"  link href="plazas">
                            <v-list-item-title> Plazas</v-list-item-title>
                            <v-list-item-icon>
                                <v-icon>mdi-file-tree-outline</v-icon>
                            </v-list-item-icon>
                        </v-list-item>
                        <v-list-item :class="submenu == 4? 'v-item--active v-list-item--active':''"  link href="oficinas">
                            <v-list-item-title> Oficinas</v-list-item-title>
                            <v-list-item-icon>
                                <v-icon>mdi-office-building-marker</v-icon>
                            </v-list-item-icon>
                        </v-list-item>
                    </v-list-group>
                </template>


                <template v-else-if="modulo == 'asistencia'">
    <!-------------------------------------------------------------------------

    --------------- / MENU DE MÓDULO DE ASISTENCIAS /------------------------------

    -------------------------------------------------------------------------->
                    <v-card-title>Asistencias</v-card-title>

                    <v-list-item href="inicio">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-view-dashboard</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Inicio</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    
                    <v-list-item href="historico">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-calendar</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Histórico</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-group
                        :value="menu == 2"
                        no-action
                    >
                        <template v-slot:activator>
                            <v-list-item-avatar>
                                <v-icon class='red darken-4 text-white' >mdi-file-chart</v-icon>
                            </v-list-item-avatar>
                            <v-list-item-title>Reporte </v-list-item-title>
                        </template>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 0? 'v-item--active v-list-item--active':''"  link href="r_asistencia" v-if="hasRole('Asistencia.supervisor|Webmaster')" v-bind="attrs" v-on="on">
                                    <v-list-item-title>Asistentes del día</v-list-item-title>
                                    <v-list-item-icon>
                                        <v-icon>mdi-account-group</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Asistentes del día</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item v-if="hasRole('Asistencia.supervisor|Webmaster')" :class="submenu == 1? 'v-item--active v-list-item--active':''"  link href="informelabor"  v-bind="attrs" v-on="on">
                                    <v-list-item-title>Informe de labor efectiva</v-list-item-title>
                                    <v-list-item-icon>
                                        <v-icon>mdi-file-tree</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Informe de labor efectiva</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 2? 'v-item--active v-list-item--active':''"  link href="informelaborusuario"  v-bind="attrs" v-on="on">
                                    <v-list-item-title>Informe de labor efectiva por usuario</v-list-item-title>
                                    <v-list-item-icon>
                                        <v-icon>mdi-account</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Informe de labor efectiva por usuario</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 3? 'v-item--active v-list-item--active':''"  link href="r_asistencia_f" v-if="hasRole('Asistencia.supervisor|Webmaster|Asistencia.JSupervisor')" v-bind="attrs" v-on="on">
                                    <v-list-item-title>Asistencia por fechas</v-list-item-title>
                                    <v-list-item-icon>
                                        <v-icon>mdi-account-group</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Asistencia por fechas</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 4? 'v-item--active v-list-item--active':''"  link href="anexo_usuario"  v-if="hasRole('Asistencia.supervisor|Webmaster')" v-bind="attrs" v-on="on">
                                    <v-list-item-title>Anexo04</v-list-item-title>
                                    <v-list-item-icon>
                                        <v-icon>mdi-file-pdf</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Anexo04</span>
                        </v-tooltip>
                    </v-list-group>
                    <v-list-item href="m_metasxmes" v-if="hasRole('Administradorb|Webmasterb|Asistencia.UsuarioMetas')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-calendar-multiple-check</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Metas mensuales</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="m_setmetas" v-if="hasRole('Asistencia.JSupervisor')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-flag-checkered</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Conf. Metas</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="m_getmetas" v-if="hasRole('Asistencia.JSupervisor')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-flag-checkered</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Metas Mensuales</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="m_jerarquia"  v-if="hasRole('Administrador|Webmaster')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-tree</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Jerarquia</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="excepciones" v-if="hasRole('Administrador|Webmaster')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-ab-testing</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Excepciones</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="feriados" v-if="hasRole('Administrador|Webmaster|Asistencia.administrador')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-calendar-blank</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Feriados</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
					<v-list-item href="configuracion" v-if="hasRole('Administrador|Webmaster|Asistencia.administrador')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-cog</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Configuración</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    
                </template>




<!--/****************************************************************** */ -->

                <template v-else-if="modulo == 'personal'">
<!-------------------------------------------------------------------------

    --------------- / MENU DE PERSONAL /------------------------------

    -------------------------------------------------------------------------->
                    <v-card-title>Personal</v-card-title>

                    <v-list-item href="organigrama">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-family-tree</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Organigrama</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-list-item href="plazas" v-if="hasRole('Personal.administrador|Asistencia.administrador|Webmaster')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-tree-outline</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Plazas Físicas</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>


                    
                    <v-list-group
                        :value="menu == 2"
                        no-action
                        v-if="hasRole('Personal.licencias|Webmaster')"
                    >
                        <template v-slot:activator>
                            <v-list-item-avatar>
                                <v-icon class='red darken-4 text-white' >mdi-wallet-travel</v-icon>
                            </v-list-item-avatar>
                            <v-list-item-title>Licencias </v-list-item-title>
                        </template>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 0? 'v-item--active v-list-item--active':''"  link href="lic_informes" v-if="hasRole('Personal.licencias|Webmaster')" v-bind="attrs" v-on="on">
                                    <v-list-item-title>Informes</v-list-item-title>
                                     <v-list-item-icon>
                                        <v-icon>mdi-book</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Informes</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 1? 'v-item--active v-list-item--active':''"  link href="vacaciones"  v-if="hasRole('Personal.licencias|Webmaster')" v-bind="attrs" v-on="on">
                                    <v-list-item-title>Vacaciones</v-list-item-title>
                                     <v-list-item-icon>
                                        <v-icon>mdi-bag-suitcase</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Vacaciones</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 2? 'v-item--active v-list-item--active':''"  link href="lic_tipos"  v-if="hasRole('Personal.licencias|Webmaster')" v-bind="attrs" v-on="on">
                                    <v-list-item-title>Tipo de Licencias</v-list-item-title>
                                     <v-list-item-icon>
                                        <v-icon>mdi-notebook-multiple</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Tipo de Licencias</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 3? 'v-item--active v-list-item--active':''"  link href="lic_formatos"  v-if="hasRole('Personal.licencias|Webmaster')" v-bind="attrs" v-on="on">
                                    <v-list-item-title>Formatos</v-list-item-title>
                                     <v-list-item-icon>
                                        <v-icon>mdi-file-word-outline</v-icon>
                                    </v-list-item-icon>
                                </v-list-item>
                            </template>
                            <span>Formatos</span>
                        </v-tooltip>
                    </v-list-group>

                    <v-list-item href="cadena" v-if="hasRole('Personal.administrador|Webmaster')" >
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-link-variant</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Cadena</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-list-item href="files" v-if="hasRole('Personal.administrador|Webmaster')" >
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-account-outline</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Files</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    
                    <v-list-group
                        :value="menu == 5"
                        no-action
                        v-if="hasRole('Webmaster')"
                    >
                        <template v-slot:activator>
                            <v-list-item-avatar>
                                <v-icon class='red darken-4 text-white' >mdi-file-tree-outline</v-icon>
                            </v-list-item-avatar>
                            <v-list-item-title>Plazas </v-list-item-title>
                        </template>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 0? 'v-item--active v-list-item--active':''"  link href="magistrados" v-if="hasRole('Personal.administrador|Webmaster')" v-bind="attrs" v-on="on">
                                    <v-list-item-title>Magistrados D.L. 276</v-list-item-title>
                                </v-list-item>
                            </template>
                            <span>Magistrados</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 1? 'v-item--active v-list-item--active':''"  link href="dl728"  v-bind="attrs" v-on="on">
                                    <v-list-item-title>DL 728</v-list-item-title>
                                </v-list-item>
                            </template>
                            <span>D.L. 728</span>
                        </v-tooltip>
                        <v-tooltip left>
                            <template v-slot:activator="{ on, attrs }">
                                <v-list-item :class="submenu == 2? 'v-item--active v-list-item--active':''"  link href="cas"  v-bind="attrs" v-on="on">
                                    <v-list-item-title>CAS DL 1057</v-list-item-title>
                                </v-list-item>
                            </template>
                            <span>C.A.S.</span>
                        </v-tooltip>
                    </v-list-group>

                </template>




<!--/****************************************************************** */ -->
                <template v-else-if="modulo == 'SIJ'">

   <!-------------------------------------------------------------------------

    --------------- / MENU DE REPORTES SIJ /------------------------------

    -------------------------------------------------------------------------->
                    <v-card-title class="title text-center pt-0 pb-2">Reportes SIJ</v-card-title>
                    <v-list-item href="inicio">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-view-dashboard</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Inicio</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="audiencias">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white'>mdi-headset-dock</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Audiencias</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="escritos">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-document-multiple</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Escritos</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="resoluciones">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-signature-freehand</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Resoluciones</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="notificaciones">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-email-send-outline</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Notificaciones</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-list-item href="usuarios" v-if="hasRole('Webmaster|Administrador|Sij.supervisor|Asistencia.JSupervisor')" >
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-account-group-outline</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Usuarios</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    
                </template>






<!--/****************************************************************** */ -->
                <template v-else-if="modulo == 'jurisprudencia'">

   <!-------------------------------------------------------------------------

    --------------- / MENU DE JURISPRUDENCIA /------------------------------

    -------------------------------------------------------------------------->
                    <v-card-title class="title text-center pt-0 pb-2">Buscador de Jurisprudencia</v-card-title>
                    
                    <v-list-item href="buscar">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-document-multiple</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Buscador</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="dashboard" v-if="hasRole('Jurisprudencia.administrador|Webmaster|Administrador')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-view-dashboard</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Registro</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>

                    <v-list-item href="config" v-if="hasRole('Webmaster|Administrador')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-cog</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Configuración</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>


                </template>




<!--/****************************************************************** */ -->
                <template v-else-if="modulo == 'surpaz'">

   <!-------------------------------------------------------------------------

    --------------- / MENU DE REPORTES SIJ /------------------------------

    -------------------------------------------------------------------------->
                    <v-card-title class="title text-center pt-0 pb-2">Jueces de Paz</v-card-title>
                    <v-list-item href="inicio">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-view-dashboard</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Inicio</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="resoluciones">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-document-multiple</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Resoluciones</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="personas">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-account-multiple</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Personas</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="comunidades">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white'>mdi-map-search-outline</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>comunidades</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </template>






<!--/****************************************************************** */ -->
                <template v-else-if="modulo == 'repolaboral'">

   <!-------------------------------------------------------------------------

    --------------- / MENU DE REPOSITORIO DE LABORAL /------------------------------

    -------------------------------------------------------------------------->
                    <v-card-title class="title text-center pt-0 pb-2">Sala Laboral</v-card-title>
                    
                    <v-list-item href="buscar_votos">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-document-multiple</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Buscador votos</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="buscar_actas">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-file-document-multiple</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Buscador actas</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="votos" v-if="hasRole('Repositorio.laboral_upd|Webmaster|Administrador')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-view-dashboard</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Registro Votos</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                    <v-list-item href="actas" v-if="hasRole('Repositorio.laboral_upd|Webmaster|Administrador')">
                        <v-list-item-avatar>
                            <v-icon class='red darken-4 text-white' >mdi-view-dashboard</v-icon>
                        </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title>Registro Actas</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </template>


<!--/****************************************************************** */ -->


            </v-list-item-group>

            <v-divider></v-divider>


            <v-list-item  link :href="modulo == 'dashboard'? 'perfil':(modulo == 'permisos_sede' || modulo == 'repolaboral')? '../../perfil' : '../perfil'">
            <v-list-item-action>
                <v-icon>mdi-account</v-icon>
            </v-list-item-action>

            <v-list-item-content>
                <v-list-item-title>
                        Perfil

                    <v-chip color="success" x-small>
                        <v-icon>mdi-alert-circle</v-icon>
                        New</v-chip>
                </v-list-item-title>
            </v-list-item-content>
            </v-list-item>
            <v-list-item  link :href="modulo == 'dashboard'? 'logout': (modulo == 'permisos_sede' || modulo == 'repolaboral')? '../../logout':'../logout'">
            <v-list-item-action>
                <v-icon>mdi-logout</v-icon>
            </v-list-item-action>

            <v-list-item-content>
                <v-list-item-title>Cerrar sesión</v-list-item-title>
            </v-list-item-content>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>
<script>
export default {
    props: ['drawer', 'n_usuario', 'menu', 'submenu', 'modulo', 'dataUser'],
    data: () => ({
        mdrawer: true,
        usuario: '',
        apellidos:'',
        email: '',
        roles:[],
        isMobile: false,
    }),
    watch: {
        drawer(val){
            this.mdrawer = val
        },
        mdrawer(val){
            this.$emit('cDrawer', val);
        },
    },
    created(){
        if(this.isMobile || window.innerWidth < 769){
            this.mdrawer= false;
            this.$emit('cDrawer', false);
        }
        //this.getData();
    },
    methods: {
        hasRole(role){
            var roles = role.split('|');
            for (let index = 0; index < roles.length; index++) {
                if (this.dataUser.modulos.includes(roles[index])) {
                    return this.dataUser.modulos.includes(roles[index]);
                }
            } 
            return false;
        },
        onResize() {
            if (window.innerWidth < 769)
                this.isMobile = true;
            else
                this.isMobile = false;
        },
    },
}
</script>