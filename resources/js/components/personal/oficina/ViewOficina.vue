<template>
   <v-card>
      <v-card>
      <v-sheet class="pa-3 primary lighten-2">
      <v-text-field
         v-model="search"
         label="Buscar Oficina"
         dark
         flat
         solo-inverted
         hide-details
         clearable
clear-icon="mdi-close-circle-outline"
      ></v-text-field>
      <v-checkbox
         v-model="caseSensitive"
         dark
         hide-details
         label="Case sensitive search"
      ></v-checkbox>
    </v-sheet>
      <v-card-text>
         <v-treeview
            :active.sync="active"
            :items="items"
            :search="search"
            :filter="filter"
            :open-all="open"
            :open.sync="openid"
            activatable
            open-on-click
            :load-children="fetchUsers"
            transition
         >
         <template v-slot:prepend="{ item, active}">
            <v-icon
            v-if="item.children"
            v-text="`${item.id === 1 ? 'business_center' : 'folder_open'}`"
            ></v-icon>
            <v-icon
              v-else-if="!item.children"
              :color="active ? 'primary' : ''"
              v-text="'perm_identity'"
            >

            </v-icon>
         </template>
      </v-treeview>
   </v-card-text>
</v-card>
   </v-card>
</template>
<script>
   const pause = ms => new Promise(resolve => setTimeout(resolve, ms))
   export default {
      props:['editedItem','editIndex'],
      data(){
         return {
            caseSensitive:false,
            search:null,
            items: [],
            active: [],
            datos:{},
            open: true,
            openid: [1, 2],
            //itemsDefault: [],
            
         };
      },
      watch:{
         editIndex(val){
            if (val<0) {
               this.items.pop()
            } else{
               this.getOficina()               
            }
         },
         selected: 'randomAvatar'
      },      
      computed:{
         filter () {
           return this.caseSensitive
             ? (item, search, textKey) => item[textKey].indexOf(search) > -1
             : undefined
         },
         selected(){

            if (!this.active.length) return undefined

            const id = this.active[0]

            //return this.personal.find(user => user.id === id)
         },
      },
      created(){
      },
      methods: {
         getOficina(url='oficinasApi?oficinaID=' + this.editedItem.ID){
            axios.get(url)
                  .then(response =>{
                     this.datos = response.data['oficinaData'];
                     
                  })
                  .catch(errors =>{
                     console.log(errors);
                  });
         },
         
         async fetchUsers (item) {
            await pause(500)
            return fetch('oficinasApi?getPersonalOfID=' + item.of_id)
                .then(res => res.json())
                .then(json => (item.children.push(...json)))
                .catch(err => console.warn(err))
         }, 
         selectOficina(oficina, id_of){
            //console.log(oficina)
            return oficina.of_id === id_of
         }
      }
   };
</script>