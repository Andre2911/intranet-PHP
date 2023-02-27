<template>
    <v-row>
        <v-col cols="12" md="2">
            <v-menu
                v-model="datetime_ini"
                :close-on-content-click="false"
                :nudge-right="40"
                transition="scale-transition"
                offset-y
                min-width="290px"
            >
                <template v-slot:activator="{ on }">
                    <v-text-field
                    v-model="f_inicio"
                    label="Fecha de Inicio"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-on="on"
                    dense
                    ></v-text-field>
                </template>
                <v-date-picker 
                    v-model="date_inicio"
                    locale="es-419"
                    @input="datetime_ini = false"
                ></v-date-picker>
            </v-menu>
        </v-col>
        <v-col cols="12" md="2">
            <v-menu
                v-model="datetime_final"
                :close-on-content-click="false"
                :nudge-right="40"
                transition="scale-transition"
                offset-y
                min-width="290px"
            >
                <template v-slot:activator="{ on }">
                    <v-text-field
                    v-model="f_final"
                    label="Fecha final"
                    prepend-icon="mdi-calendar"
                    readonly
                    v-on="on"
                    dense
                    ></v-text-field>
                </template>
                <v-date-picker 
                    v-model="date_final"
                    locale="es-419"
                    @input="datetime_final = false"
                ></v-date-picker>
            </v-menu>
        </v-col>
        <v-col cols="12" md="2">
            <v-autocomplete
                v-model="x_nom_provincia"
                label="PROVlNCIA"
                :items="filtroProvincia"
                item-text="x_nom_provincia"
                item-value="x_nom_provincia"
                dense
            />
        </v-col>
        <v-col cols="12" md="3">
            <v-autocomplete
                v-model="c_sede"
                label="Sede Judicial"
                :items="filtroSede"
                item-text="x_desc_sede"
                item-value="c_sede"
                :disabled="x_nom_provincia == null"
                dense
            />
        </v-col>
        <v-col cols="12" md="3">
            <v-autocomplete
                v-model="c_org_jurisd"
                label="Organo Jurisdiccional"
                :items="filtroOrgJur"
                item-text="x_nom_org_jurisd"
                item-value="c_org_jurisd"
                :disabled="c_sede == null"
                dense
                />
        </v-col>
        <v-col cols="12" md="4" class="pt-0 pb-0">
            <v-autocomplete
                v-model="c_especialidad"
                label="Especialidad"    
                :items="filtroEspecialidad"
                item-text="x_desc_especialidad"
                item-value="c_especialidad"
                :disabled="c_org_jurisd == null"
                multiple
                deletable-chips
                clearable
                open-on-clear
                chips
                dense
                prepend-icon="mdi-database-search"
            >
                <template v-slot:append-outer>
                    <v-slide-x-reverse-transition
                        mode="out-in"
                    >
                        <v-icon
                            @click="selectAllEspecialidad"
                        >mdi-spellcheck</v-icon>
                    </v-slide-x-reverse-transition>
                </template>
            </v-autocomplete>
        </v-col>
        <v-col cols="12" md="5" class="pt-0 pb-0">
            <v-autocomplete
                v-model="c_instancia"
                label="Instancia"    
                :items="filtroInstancia"
                item-text="x_nom_instancia"
                item-value="c_instancia"                        
                :disabled="c_especialidad == null"
                multiple
                deletable-chips
                clearable
                open-on-clear
                chips
                dense
            >
                <template v-slot:append-outer>
                    <v-slide-x-reverse-transition
                        mode="out-in"
                    >
                        <v-icon
                            @click="selectAllInstancia"
                        >mdi-spellcheck</v-icon>
                    </v-slide-x-reverse-transition>
                </template>
            </v-autocomplete>
        </v-col>
        <v-col cols="12" md="3" class="pt-0 pb-0">
            <v-btn block :disabled="c_instancia == null || c_instancia.length == 0" @click="getData()" dense>
                <v-icon>mdi-magnify</v-icon>
                Buscar {{type}}
            </v-btn>
        </v-col>
    </v-row>
</template>
<script>
export default {
    props:['type', 'dataUser'],
    data() {
        return {
            instancias: [],
            x_nom_provincia: null,
            c_sede:null,
            c_org_jurisd:null,
            c_especialidad:null,
            c_instancia:null,
            datetime_ini: false,
            f_inicio: this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10))),
            date_inicio: this.parseDate(new Date().toLocaleDateString()),
            datetime_final: false,
            f_final: this.formatDate(this.parseDate(new Date().toLocaleDateString().substr(0, 10))),
            date_final: this.parseDate(new Date().toLocaleDateString()),
        }
    },
    computed:{
        filtroProvincia(){
            if(this.instancias[0] != undefined){
                let provincias = this.instancias;
                if(provincias.length > 0){
                    return provincias.filter(d => d.x_nom_provincia);
                } else{
                    return [];
                }
            } else{
                return [];
            }
        },
        filtroSede(){
            let sede = this.instancias;
            return sede.filter(d => d.x_nom_provincia == this.x_nom_provincia);
        },
        filtroOrgJur(){
            let orgJur = this.instancias;
            return orgJur.filter(d => d.x_nom_provincia == this.x_nom_provincia  && d.c_sede == this.c_sede);
        },
        filtroEspecialidad(){
            let especialidad = this.instancias;
            return especialidad.filter(d => d.x_nom_provincia == this.x_nom_provincia  && d.c_sede == this.c_sede && d.c_org_jurisd == this.c_org_jurisd);
        },
        filtroInstancia(){
            let instancias = this.instancias;
            let especialidades = this.c_especialidad;

            let res = [];
            let instanciasf1 = [];

            if(this.c_especialidad != null){
                instanciasf1 = instancias.filter(d => 
                        d.x_nom_provincia == this.x_nom_provincia  && 
                        d.c_sede == this.c_sede && 
                        d.c_org_jurisd == this.c_org_jurisd);

                res = instanciasf1.filter(el => {
                    return especialidades.find(element => {
                        return element === el.c_especialidad;
                    });
                });
            }

            return res;
            
           
            
        },
        
        c_provincia(){
            if(this.c_instancia != null){
                let instanciasf1 = []
                let instanciasSelected = this.c_instancia;

                instanciasf1 = this.filtroInstancia.filter(el => {
                    return instanciasSelected.find(element => {
                        return element === el.c_instancia;
                    });
                });


                if(instanciasf1.length == 1){
                    return instanciasf1[0]['c_provincia'];
                } else if(instanciasf1.length > 1){
                    return instanciasf1[0]['c_provincia'];
                } else{
                    return null;
                }
            } else{
                return null;
            }
        },
    },
    watch:{
        date_inicio (val) {
            this.f_inicio = this.formatDate(this.date_inicio)
        },
        date_final (val) {
            this.f_final = this.formatDate(this.date_final)
        },
        x_nom_provincia(val){
            this.c_instancia = null
            this.c_especialidad = null
        },
        c_sede(val){
            this.c_especialidad = null
            this.c_instancia = null
        },
        c_org_jurisd(val){
            this.c_especialidad = null
            this.c_instancia = null
        },
        c_especialidad(val){
            this.c_instancia = null
        },
    },
    created(){
        this.getInstancias();
    },
    methods: {
        getInstancias(){
            this.showLoad('grey', 'Cargando datos');
            let url = '';
            if (this.hasRole('Administrador|SIJ.supervisor')){
                url = '../consultasSIJ/listarInstancias';
            } else{
                url = '../consultasSIJ/listarInstanciasUser?c_dni='+this.dataUser.usuario.numero_documento;
            }
            axios.get(url)
                .then(response =>{
                    if(response.data['status'] != 0){
                        this.instancias = response.data['data'];
                    } else{
                        this.instancias = []
                        this.showSnack('warning',response.data['message'] )
                    }
                    this.showLoad('','', false);
                })
                .catch(errors =>{
                    console.log(errors);
                    if (errors.message === "Network Error"){this.showSnack('warning', 'No se pudo conectar con el servicio de Base de Datos del SIJ')}
                    this.showLoad('','', false);

                });
        },
        selectAllEspecialidad(){
            var especialidades = [];
            if(this.filtroEspecialidad[0] != undefined){
                especialidades = this.filtroEspecialidad.reduce(function (allEspecialidades, especialidad) {
                    let indexOfAud = allEspecialidades.map(function(e) { return e; }).indexOf(especialidad.c_especialidad);
                    if (indexOfAud == -1) {
                        allEspecialidades.push(especialidad.c_especialidad);
                    }
                    return allEspecialidades
                }, [])
            }   

            this.c_especialidad = [...especialidades]
        },
        selectAllInstancia(){
            var instancias = [];
            if(this.filtroInstancia[0] != undefined){
                instancias = this.filtroInstancia.reduce(function (allInstancias, instancia) {
                    let indexOfAud = allInstancias.map(function(e) { return e; }).indexOf(instancia.c_instancia);
                    if (indexOfAud == -1) {
                        allInstancias.push(instancia.c_instancia);
                    }
                    return allInstancias
                }, [])
            }   

            this.c_instancia = [...instancias]
        },
        getData(){
            this.$emit('getData', this.c_provincia, this.c_sede, this.c_org_jurisd, this.c_especialidad, this.c_instancia, this.f_inicio, this.f_final);
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
        showSnack(color, text, show = true){
            this.$emit('showSnack', color, text, show)
        },
        showLoad(color, text, show = true){
            this.$emit('showLoad', color, text, show)
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
    },
}
</script>