/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue'

import Vuetify from '../plugins/vuetify';

import 'vuetify/dist/vuetify.min.css'

import VueHtmlToPaper from "vue-html-to-paper";
import JsonExcel from "vue-json-excel";
import HighchartsVue from 'highcharts-vue';
import Highcharts from 'highcharts';


import exportingInit from 'highcharts/modules/exporting'
import OrganizationInit from 'highcharts/modules/organization'
import sanKeyInit from 'highcharts/modules/sankey';


sanKeyInit(Highcharts)
exportingInit(Highcharts)
OrganizationInit(Highcharts)

const options = {
    name: "_blank",
    specs: ["fullscreen=yes", "titlebar=yes", "scrollbars=yes"],
    styles: [
        "../public/iof/css/bootstrap.min.css",
        "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css",
        "https://unpkg.com/kidlat-css/css/kidlat.css",
        "https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css"
    ]
};


Vue.use(VueHtmlToPaper, options, HighchartsVue.default);

window.Vue = require('vue');


Vue.component('header-component', require('./components/layouts/HeaderComponent.vue').default);
Vue.component('menu-component', require('./components/layouts/MenuComponent.vue').default);
Vue.component('footer-component', require('./components/layouts/FooterComponent.vue').default);

Vue.component('dashboard-component', require('./components/DashboardComponent.vue').default);



/*--------------------------------------------------------------------------
| Rutas Modulos ASISTENCIAS
|--------------------------------------------------------------------------*/
Vue.component('asistencia-component', require('./components/asistencia/IniAsistComponent.vue').default);

Vue.component('historico-asistencia-component', require('./components/asistencia/HisAsistenciaComponent.vue').default);
Vue.component('asistencia-reporte', require('./components/asistencia/ReporteComponent.vue').default);
Vue.component('asistencia-reporte-dia', require('./components/asistencia/ReporteAsistenciaDia.vue').default);
Vue.component('asistencia-reporte-labor', require('./components/asistencia/ReporteInformeFechaUsuario.vue').default);
Vue.component('asistencia-reporte-labores', require('./components/asistencia/ReporteInformeFecha.vue').default);
Vue.component('asistencia-reporte-fecha', require('./components/asistencia/ReporteAsistenciaFecha.vue').default);

Vue.component('asistencia-reporte-anexo', require('./components/asistencia/metas/ReporteAnexo.vue').default);

Vue.component('asistencia-excepcion', require('./components/asistencia/Excepciones.vue').default);
Vue.component('asistencia-feriados', require('./components/asistencia/Feriados.vue').default);
Vue.component('asistencia-configuracion', require('./components/asistencia/Config.vue').default);

/********************* METAS */
Vue.component('asistencia-jerarquia', require('./components/asistencia/metas/Jerarquia.vue').default);
Vue.component('asistencia-metas', require('./components/asistencia/metas/Metas.vue').default);
Vue.component('asistencia-metasxmes', require('./components/asistencia/metas/MetasXmes.vue').default);
Vue.component('asistencia-metas-trabajadores', require('./components/asistencia/metas/MetasTrabajadores.vue').default);






/*--------------------------------------------------------------------------
| Rutas Modulos BOLETAS DE PERMISO
|--------------------------------------------------------------------------*/


/*--------------------------------------------------------------------------
| Rutas Modulos ADMINISTRACIÓN DE PERSONAL
|--------------------------------------------------------------------------*/

Vue.component('crud-oficina-component', require('./components/personal/oficina/CrudOficina.vue').default);
Vue.component('view-oficina-component', require('./components/personal/oficina/ViewOficina.vue').default);
Vue.component('new-oficina-component', require('./components/personal/oficina/NewOficina.vue').default);
Vue.component('select-oficina-component', require('./components/personal/oficina/SelectOficina.vue').default);

Vue.component('organigrama-personal', require('./components/personal/Organigrama.vue').default);


Vue.component('crud-personal-component', require('./components/personal/personal/CrudPersonal.vue').default);
Vue.component('new-personal-component', require('./components/personal/personal/NewPersonal.vue').default);
Vue.component('select-personal-component', require('./components/personal/personal/SelectPersonal.vue').default);

Vue.component('plazas-personal', require('./components/personal/plazas/PlazasComponent.vue').default);
Vue.component('adm-plazas-fisica', require('./components/personal/plazas/AdmPlazaF.vue').default);
Vue.component('personal-plazas-new-usuario', require('./components/personal/plazas/NewUsuario.vue').default);


Vue.component('show-personal-component', require('./components/personal/ShowComponent.vue').default);
Vue.component('show-personal-filter', require('./components/personal/ShowComponentFilter.vue').default);
Vue.component('organigrama-component', require('./components/personal/Organigrama.vue').default);
Vue.component('move-organigrama-plaza', require('./components/personal/OrganigramaPlaza.vue').default);


Vue.component('config-local-component', require('./components/personal/configuracion/Local.vue').default);
Vue.component('new-local-component', require('./components/personal/configuracion/NewLocal.vue').default);


Vue.component('personal-cadena', require('./components/personal/cadena/Inicio.vue').default);
Vue.component('personal-cadena-suplente', require('./components/personal/cadena/SelectSuplente.vue').default);

Vue.component('personal-files', require('./components/personal/files/Inicio.vue').default);


/*******lICENCIAS */
Vue.component('personal-licencias-informes', require('./components/personal/licencias/Informes.vue').default);
Vue.component('personal-licencias-newInforme', require('./components/personal/licencias/NewInforme.vue').default);
Vue.component('personal-licencias-tipos', require('./components/personal/licencias/TipoLicencia.vue').default);
Vue.component('personal-licencias-newTipo', require('./components/personal/licencias/NewTipoLicencia.vue').default);
Vue.component('personal-licencias-formatos', require('./components/personal/licencias/Formatos.vue').default);
Vue.component('personal-licencias-formato-new', require('./components/personal/licencias/NewFormato.vue').default);
Vue.component('personal-vacaciones', require('./components/personal/licencias/Vacaciones.vue').default);




/*--------------------------------------------------------------------------
| Rutas Modulo COURIER - TRÁMITE DOCUMENTARIO
|--------------------------------------------------------------------------*/


/*--------------------------------------------------------------------------
| Rutas Modulo FLOTA VEHICULAR
|--------------------------------------------------------------------------*/

/*--------------------------------------------------------------------------
| Rutas Modulo SIJ
|--------------------------------------------------------------------------*/

Vue.component('sij-dashboard', require('./components/SIJ/Dashboard.vue').default);
Vue.component('sij-parametros', require('./components/SIJ/Parametros.vue').default);
Vue.component('sij-audiencias', require('./components/SIJ/audiencias/Inicio.vue').default);
Vue.component('sij-audiencias-view', require('./components/SIJ/audiencias/ViewAudiencia.vue').default);
Vue.component('sij-escritos', require('./components/SIJ/escritos/Inicio.vue').default);
Vue.component('sij-escritos-view', require('./components/SIJ/escritos/ViewEscrito.vue').default);

Vue.component('sij-resoluciones', require('./components/SIJ/resoluciones/Inicio.vue').default);
Vue.component('sij-resoluciones-view', require('./components/SIJ/resoluciones/ViewResolucion.vue').default);

Vue.component('sij-notificaciones', require('./components/SIJ/notificaciones/Inicio.vue').default);
Vue.component('sij-notificaciones-view', require('./components/SIJ/notificaciones/ViewNotificacion.vue').default);

Vue.component('sij-usuario', require('./components/SIJ/usuario/Inicio.vue').default);
Vue.component('sij-usuario-general', require('./components/SIJ/usuario/R1General.vue').default);
Vue.component('sij-usuario-resoluciones', require('./components/SIJ/usuario/R2Resoluciones.vue').default);
Vue.component('sij-usuario-audiencias', require('./components/SIJ/usuario/R3Audiencias.vue').default);
Vue.component('sij-usuario-escritos', require('./components/SIJ/usuario/R4Escritos.vue').default);


Vue.component('sij-cupones', require('./components/SIJ/cupones/Inicio.vue').default);




/*--------------------------------------------------------------------------
| Rutas Modulos JURISPRUDENCIA
|--------------------------------------------------------------------------*/

/* Resoluciones **/

Vue.component('jurisprudencia-registro', require('./components/repositorio/jurisprudencia/RepositorioComponent.vue').default);
Vue.component('show-repositorio-component', require('./components/repositorio/jurisprudencia/ShowRepositorio.vue').default);
Vue.component('jurisprudencia-buscador', require('./components/repositorio/jurisprudencia/BuscarComponent.vue').default);
Vue.component('show-repositorio-buscar', require('./components/repositorio/jurisprudencia/ShowBusqueda.vue').default);

Vue.component('jurisprudencia-config', require('./components/repositorio/jurisprudencia/Config.vue').default);




/*--------------------------------------------------------------------------
| Rutas Modulos REPOSITORIO PRIVADO
|--------------------------------------------------------------------------*/

Vue.component('repositorio-dashboard', require('./components/repositorio/Dashboard.vue').default);


/*--------------------------------------------------------------------------
| Rutas Modulos REPOSITORIO - LABORAL
|--------------------------------------------------------------------------*/


Vue.component('repositorio-laboral-registro', require('./components/repositorio/laboral/votos/Registro.vue').default);
Vue.component('repositorio-laboral-buscador', require('./components/repositorio/laboral/votos/Buscar.vue').default);
Vue.component('repositorio-laboral-resultados', require('./components/repositorio/laboral/votos/Resultados.vue').default);

Vue.component('repositorio-laboral-actas', require('./components/repositorio/laboral/actas/Buscar.vue').default);
Vue.component('repositorio-laboral-actas-registro', require('./components/repositorio/laboral/actas/Registro.vue').default);



/*--------------------------------------------------------------------------
| Rutas Modulo JUECES DE PAZ 
|--------------------------------------------------------------------------*/



/*--------------------------------------------------------------------------
| Rutas Modulo MANTENIMIENTO
|--------------------------------------------------------------------------*/

Vue.component('dashboard-mantenimiento', require('./components/mantenimiento/DashboardMantenimiento.vue').default);

//Vue.component('admin-dashboard-component', require('./components/usermpv/DashboardComponent.vue').default);

Vue.component('admin-usuarios-component', require('./components/mantenimiento/usuarios/UsuariosComponent.vue').default);
Vue.component('new-usuarios-component', require('./components/mantenimiento/usuarios/NewUsuario.vue').default);

Vue.component('admin-personas-component', require('./components/mantenimiento/personas/PersonasComponent.vue').default);
Vue.component('new-persona-component', require('./components/mantenimiento/personas/NewPersona.vue').default);
Vue.component('select-persona-component', require('./components/mantenimiento/personas/SelectPersona.vue').default);

Vue.component('admin-roles-component', require('./components/mantenimiento/roles/RolesComponent.vue').default);
Vue.component('new-roles-component', require('./components/mantenimiento/roles/NewRol.vue').default);
Vue.component('select-roles-component', require('./components/mantenimiento/roles/SelectRol.vue').default);

Vue.component('admin-plazas-component', require('./components/mantenimiento/plazas/PlazasComponent.vue').default);
Vue.component('new-plazas-component', require('./components/mantenimiento/plazas/NewPlaza.vue').default);
Vue.component('select-plazas-component', require('./components/mantenimiento/plazas/SelectPlaza.vue').default);

Vue.component('admin-oficinas-component', require('./components/mantenimiento/oficinas/OficinaComponent.vue').default);
Vue.component('new-oficina-component', require('./components/mantenimiento/oficinas/NewOficina.vue').default);



/************ PERFIL DE USUARIO */

Vue.component('usuario-perfil', require('./components/usuario/PerfilComponent.vue').default);
Vue.component('usuario-pprofesional', require('./components/usuario/PerfilProfesional.vue').default);


/***********TOOLS************** */
Vue.component('snackbar', require('./components/tools/SnackBarComponent.vue').default);
Vue.component('dialogLoader', require('./components/tools/DialogLoad.vue').default);
Vue.component('dataTable', require('./components/tools/DataTableComponent.vue').default);
Vue.component('dataTableFull', require('./components/tools/DataTableFull.vue').default);
Vue.component('dataTableSIJ', require('./components/tools/DataTableSIJ.vue').default);
Vue.component("downloadExcel", JsonExcel);


const app = new Vue({
    vuetify: Vuetify,
    el: '#app',
    data: {
        isLoading: true
    },

    mounted() {
        setTimeout(() => {
            this.isLoading = false
        }, 5000)
    }
});