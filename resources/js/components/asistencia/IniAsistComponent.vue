<template>
	<v-app id="inspire">
				<header-component :drawer='drawer' @cDrawer='cDrawer' :modulo="modulo"/>

		<menu-component :drawer='drawer' @cDrawer='cDrawer' :menu='menu' :submenu='submenu' :modulo="modulo" :dataUser="dataUser"/>
		<v-main id="contentApp">
			<v-card>
				<v-toolbar color="grey" dark>
					<v-toolbar-title>Asistencia de Personal</v-toolbar-title>
					<v-spacer/>
					<v-btn color="red accent-2" @click="iniciarDia()">
						<v-icon>mdi-refresh</v-icon>
						Actualizar
					</v-btn>
				</v-toolbar>
					<v-container>
						<v-avatar
							slot="icon"
							color="red darken-4"
							size="50"
							>
							<v-icon
								color="white"
							>
								mdi-calendar
							</v-icon>
						</v-avatar>
						<b>{{fecha}}</b>
						<b>{{getHora}}</b>
						<v-layout wrap>
						</v-layout>
						<v-layout v-if="enable_asistencia==true && (in_red == true || in_remoto ==  true || is_excepcional == true)" wrap>
							
							<v-flex xs12 md4 sm12 :class="(tipo_asistencia*1 == 2 )? 'offset-sm2' :''">
<!--------------  BOTON INGRESO *********************************************        -->

								<v-card
									class="ma-3 pa-1 none-decoration"
									:color="ingreso == ''? 'primary': 'info'" dark
									tile shaped
									elevation="15" link
									:disabled="ingreso != ''"
									:hover="ingreso == ''"
									@click="ingreso == ''? registrarIngreso_p1(): ''"
								>
								
									<div class="d-flex flex-no-wrap justify-space-between">
										<div>
											<v-card-title>
												<div class="headline">Ingreso</div><v-divider></v-divider>
											</v-card-title>

											<v-card-actions>
												<v-layout wrap>
													<v-flex xs12>
														<span> {{ingreso == ''? 'Clic aquí para registrar su Ingreso':'Asistencia registrada'}}</span>
													</v-flex>
													<v-flex xs12>
														<span  
														class="headline"
														v-if="ingreso != ''">
															<template v-if="tipo_asistencia == 1 || tipo_asistencia == '1'">
																TRABAJO PRESENCIAL
															</template>
															<template v-else>
																TRABAJO REMOTO
															</template>
														</span>
													</v-flex>
													<v-flex xs12>
														<v-chip  
														v-if="ingreso != ''">
															<b class="headline">{{ingreso}}</b>
														</v-chip>
													</v-flex>
												</v-layout>
											</v-card-actions>
										</div>
										<v-avatar
											class="ma-3"
											size="125"
											tile
										>
											<v-icon size="125">mdi-clock-check-outline</v-icon>
										</v-avatar>
									</div>
								
								</v-card>
							</v-flex>
<!----- FINAL BOTON INGRESO *********************************************        -->

							<v-flex xs12  md4 sm12 v-if="enable_refrigerio && tipo_asistencia == 1">
								<v-layout wrap>
									<v-flex xs12sm12>
										<v-card
											class="mt-3 pa-1 none-decoration"
											outlined :color="(refrg_ini == '' && salida == '')? 'warning': 'gray'" dark
											tile shaped
											elevation="15" link
											:disabled="refrg_ini != '' || salida != ''"
											:hover="refrg_ini == '' && salida == ''"
											@click="(refrg_ini == ''&& salida == '')? registrarRefrigerio(): ''"
											v-if="ingreso != ''  && tipo_asistencia == 1 && enable_refrigerio"
										>
											<div class="d-flex flex-no-wrap justify-space-between">
												<div>
													<v-card-title>
														<div class="headline">Inicio Refrigerio</div>
													</v-card-title>
													<v-card-actions>
														<v-layout wrap>
															<v-flex xs12>
																<span> {{refrg_ini == ''? 'Clic aquí para registrar el inicio de su refrigerio':'Salida a refrigerio registrada'}}</span>
															</v-flex>
															<v-flex xs12>
																<v-chip v-if="refrg_ini != ''"><b class="headline">{{refrg_ini}}</b></v-chip>
															</v-flex>
														</v-layout>
													</v-card-actions>
												</div>
												<v-avatar
													class="ma-3"
													size="75"
													tile
												>
													<v-icon size="75">mdi-fast-forward</v-icon>
												</v-avatar>
											</div>
										</v-card>
									</v-flex>
									<v-flex xs12 sm12>
										<v-card
											class="mt-3 pa-1 none-decoration"
											outlined :color="(refrg_fin == '' && salida == '')? 'cyan': 'gray'" dark
											tile shaped
											elevation="15" link
											:disabled="refrg_fin != '' || salida != ''"
											:hover="refrg_fin == '' && salida == ''"
											@click="(refrg_fin == '' && salida == '')? registrarRefrigerioR(): ''"
											v-if="refrg_ini!= '' && ingreso != '' && tipo_asistencia == 1 && enable_refrigerio_r"

										>
											<div class="d-flex flex-no-wrap justify-space-between">
												<div>
													<v-card-title>
														<div class="headline">Retorno Refrigerio</div>
													</v-card-title>
													<v-card-actions>
														<v-layout wrap>
															<v-flex xs12>
																<span> {{refrg_fin == ''? 'Clic aquí para registrar el retorno de su refrigerio':'Retorno de refrigerio registrado'}}</span>
															</v-flex>
															<v-flex xs12>
																<v-chip v-if="refrg_fin != ''"><b class="headline">{{refrg_fin}}</b></v-chip>
															</v-flex>
														</v-layout>
													</v-card-actions>
												</div>
												<v-avatar
													class="ma-3"
													size="75"
													tile
												>
													<v-icon size="75">mdi-rewind</v-icon>
												</v-avatar>
											</div>
										</v-card>
									</v-flex>
								</v-layout>
							</v-flex>
							
<!-------------- TARJETA BOTON SALIDA *********************************************        -->
							
							<v-flex xs12 md4 sm12>
								<v-card
									class="ma-3 pa-1 none-decoration"
									outlined :color="salida == ''? 'success':'teal accent-4'" dark
									tile shaped
									:hover="salida == ''"
									elevation="15" link
									v-if="
									ingreso != '' && 
									enable_salida && 
									(
										(tipo_asistencia*1 == 1 && in_red == true) 
										|| (tipo_asistencia*1 == 2) 
										|| (magistrado == true && ingreso != '' ) 
										|| is_excepcional
										|| salida != ''
									)"
									:disabled="salida != ''"
									@click="(salida == '')? (tipo_asistencia == 1 || tipo_asistencia == '1' || magistrado == true || magistrado == 'true' )? registrarSalida(): consultarSalida() : ''"
								>
								
									<div class="d-flex flex-no-wrap justify-space-between">
										<div>
											<v-card-title>
												<div class="headline">Salida</div>
											</v-card-title>
											<v-card-actions>
												<v-layout wrap>
													<v-flex xs12>
														<span> {{salida == ''? 'Clic aquí para registrar su Salida':'Salida registrada'}}</span>
													</v-flex>
													<v-flex xs12>
														<v-chip v-if="salida != ''"><b class="headline">{{salida}}</b></v-chip>
													</v-flex>
												</v-layout>
											</v-card-actions>
										</div>
										<v-avatar
											class="ma-3"
											size="125"
											tile
										>
											<v-icon size="125">mdi-exit-run</v-icon>
										</v-avatar>
									</div>
								</v-card>

								<v-card v-else-if="ingreso != '' && enable_salida && tipo_asistencia*1 == 1 && in_red == false && salida == ''"
									class="ma-3 pa-1 none-decoration"
									outlined :color="gray" dark
									tile shaped
									disabled
								>
									<div class="d-flex flex-no-wrap justify-space-between">
										<div>
											<v-card-title>
												<div class="headline">Salida</div>
											</v-card-title>
											<v-card-actions>
												<v-layout wrap>
													<v-flex xs12>
														<span> Para registrar la salida presencial tiene que hacerlo en su computadora dentro de la SEDE</span>
													</v-flex>
													<v-flex xs12>
														<v-chip v-if="salida != ''"><b class="headline">{{salida}}</b></v-chip>
													</v-flex>
												</v-layout>
											</v-card-actions>
										</div>
										<v-avatar
											class="ma-3"
											size="125"
											tile
										>
											<v-icon size="125">mdi-exit-run</v-icon>
										</v-avatar>
									</div>
								</v-card>
									
							</v-flex>

<!-------------- FINAL TARJETA BOTON SALIDA *********************************************        -->


							<!-----------------
		-----------------------------------------------------
		INICIO  REMOTO 2
		----------------------------------------------------
		-------------->

		<!---
							<v-flex xs12 md4 sm12 v-if="salida != '' && enable_ing_remoto2 == true && registra_metas_dia == false">
								<v-layout wrap>
									<v-flex xs12 sm12 v-if="enable_ing_remoto2 == true ">
										<v-card
											class="mt-3 pa-1 none-decoration"
											outlined :color="(salida != '' && enable_ing_remoto2)? 'warning': 'gray'" dark
											tile shaped
											elevation="15" link
											:disabled="enable_ing_remoto2 != true || remoto2_ini !=''"
											:hover="salida != '' && enable_ing_remoto2 && remoto2_ini == ''"
											@click="(salida != '' && enable_ing_remoto2)? registrarComplementarioI(): ''"
											v-if="salida != ''  && tipo_asistencia == 1 && enable_ing_remoto2"
										>
											<div class="d-flex flex-no-wrap justify-space-between">
												<div>
													<v-card-subtitle>
														<h5 class="">Remoto complementario [Ingreso]</h5>
													</v-card-subtitle>
													<v-card-actions>
														<v-layout wrap>
															<v-flex xs12>
																<span> {{remoto2_ini == ''? 'Clic aquí para registrar el inicio complementario remoto':'Ingreso a Remoto complementario iniciado'}}</span>
															</v-flex>
															<v-flex xs12>
																<v-chip v-if="remoto2_ini != ''"><b class="headline">{{remoto2_ini}}</b></v-chip>
															</v-flex>
														</v-layout>
													</v-card-actions>
												</div>
												<v-avatar
													class="ma-3"
													size="75"
													tile
												>
													<v-icon size="75">mdi-home-city-outline</v-icon>
												</v-avatar>
											</div>
										</v-card>
									</v-flex>
									<v-flex xs12 sm12 v-if="salida != '' && enable_ing_remoto2 == true && remoto2_ini != ''">
										<v-card
											class="mt-3 pa-1 none-decoration"
											outlined :color="(salida!= '' && remoto2_ini != '' && tipo_asistencia == 1 && enable_sal_remoto2 == true)? 'cyan': 'gray'" dark
											tile shaped
											elevation="15" link
											:disabled="salida == '' && remoto2_ini == '' && tipo_asistencia != 1 || !enable_sal_remoto2 || remoto2_fin != ''"
											@click="(salida!= '' && remoto2_ini != '' && tipo_asistencia == 1 && enable_sal_remoto2 == true && remoto2_fin == '')? consultarSalida(): ''"
											v-if="salida!= '' && remoto2_ini != '' && tipo_asistencia == 1"

										>
											<div class="d-flex flex-no-wrap justify-space-between">
												<div>
													<v-card-subtitle>
														<h5 class="">Remoto complementario [Salida]</h5>
													</v-card-subtitle>
													<v-card-actions>
														<v-layout wrap>
															<v-flex xs12>
																<span v-if="salida!= '' && remoto2_ini != '' && tipo_asistencia == 1 && enable_sal_remoto2"> {{remoto2_fin == ''? 'Clic aquí para registrar la salida':'Salida de remoto complementario registrado'}}</span>
																<span v-else>
																	Podrá marcar salida de remoto complementario pasada las 3 horas del inicio del complementario remoto [{{remoto2_ini}}] + 03:00:00
																</span>
															</v-flex>
															<v-flex xs12>
																<v-chip v-if="remoto2_fin != ''"><b class="headline">{{remoto2_fin}}</b></v-chip>
															</v-flex>
														</v-layout>
													</v-card-actions>
												</div>
												<v-avatar
													class="ma-3"
													size="75"
													tile
												>
													<v-icon size="75">mdi-exit-to-app</v-icon>
												</v-avatar>
											</div>
										</v-card>
									</v-flex>
								</v-layout>
							</v-flex>

--->

		<!-----------------
		-----------------------------------------------------
		FIN REMOTO 2
		----------------------------------------------------
		-------------->
						</v-layout>


						<v-layout v-else-if="enable_asistencia == false">
							<v-flex xs12 sm12>
								<v-alert
								:value="true"
								color="warning"
								icon="mdi-alert-octagram"
								outlined
								class="headline"
								>

								Ud. no tiene asistencia programada para el día de hoy {{fecha}}
								</v-alert>
							</v-flex>
						</v-layout>
						<v-layout v-else-if="in_red == false">
							<v-flex xs12 sm12>
								<v-alert
								:value="true"
								color="warning"
								icon="mdi-alert-octagram"
								outlined
								class="headline"
								dark
								>

								Ud. no esta dentro de la Red de la Corte, la asistencia debe realizarse desde su computadora o a traves de FORTICLIENT en caso de realizar trabajo remoto
								</v-alert>
							</v-flex>
						</v-layout>

					
					<!--<v-layout>
						<v-flex xs12 sm12>
							<v-alert
							:value="true"
							color="info"
							icon="priority_high"
							outline
							>

							A partir del día 02/12/2020 se deberá marcar adicionalmente el inicio (13:00 horas) y retorno de refrigerio (13:45 horas)
							</v-alert>
						</v-flex>
					</v-layout>
					-->
					
				</v-container>

			</v-card>
		</v-main>
        <footer-component/>

		<v-dialog v-model="d_tipo_asistencia" max-width="500">
			<v-card>	
				<v-layout wrap>
						<v-flex xs12 sm12 v-if="(in_red == false && in_remoto == true) || is_excepcional == true  || magistrado == true">
							<template v-if="enable_ing_remoto == true || magistrado == true">
								<v-card
									class="ma-3 pa-1 none-decoration"
									tile shaped hover
									elevation="15" link
									@click="ingreso == ''? registrarIngreso(2): ''"
								>
								
									<div class="d-flex flex-no-wrap justify-space-between">
										<div>
											<v-card-title>
												<div class="headline">REMOTO</div>
											</v-card-title>
											<v-card-actions>
												<v-layout wrap>
													<v-flex xs12>
														<span> Clic aquí si hará trabajo remoto</span>
													</v-flex>
												</v-layout>
											</v-card-actions>
										</div>
										<v-avatar
											class="ma-3"
											size="125"
											tile
										>
											<v-icon size="125">mdi-home-city-outline</v-icon>
										</v-avatar>
									</div>
								</v-card>
							</template>
							<template v-else>
								<v-card
									class="ma-3 pa-1 none-decoration"
									tile shaped hover
									elevation="15" link
									color="grey" dark
									disabled
								>
								
									<div class="d-flex flex-no-wrap justify-space-between">
										<div>
											<v-card-title>
												<div class="headline">REMOTO</div>
											</v-card-title>
											<v-card-actions>
												<v-layout wrap>
													<v-flex xs12>
														<span><b> La asistencia remota se habilitará desde las 7:45 am </b></span>
													</v-flex>
												</v-layout>
											</v-card-actions>
										</div>
										<v-avatar
											class="ma-3"
											size="125"
											tile
										>
											<v-icon size="125">mdi-home-city-outline</v-icon>
										</v-avatar>
									</div>
								</v-card>
							</template>
						</v-flex>
						<v-flex xs12 sm12 v-else>
							<v-card
								class="ma-3 pa-1 none-decoration"
								tile shaped hover
								elevation="15" link
								>
								<v-alert :value="true"
									color="warning"
									icon="mdi-alert-decagram"
									dark>
									Para marcar asistencia remota, tiene que hacerlo a traves del FORTICLIENT
								</v-alert>
							</v-card>
						</v-flex>
        			</v-layout>
				</v-card>
		</v-dialog>

		<v-dialog v-model="d_actividades" max-width="900" transition="dialog-transition">
			<v-card>
				 <v-toolbar color="red darken-1" dark>
					<v-toolbar-title v-if="enable_sal_metas == true && tipo_asistencia*1 == 1 ">CUMPLIMIENTO DE METAS ANEXO 04</v-toolbar-title>
					<v-toolbar-title v-else>INFORME DE CUMPLIMIENTO DE LABOR EFECTIVA - TRABAJO REMOTO </v-toolbar-title>
					<v-spacer></v-spacer>
                        <v-icon  @click="d_actividades = false">mdi-close</v-icon>
				</v-toolbar>
					<v-card-text>
<!----------- **********  METAS MENSUALES  **************-->
						<template v-if="hasRole('Asistencia.UsuarioMetas')">
							<v-container grid-list-md>
								<v-list dense>
									<v-subheader>Actividades programadas</v-subheader>
									<v-alert
										:value="true"
										color="primary"
										icon="mdi-alert-decagram"
										dark
										>
										Complete la cantidad de las actividades no verificables en el SIJ y en caso este haciendo TRABAJO REMOTO el tiempo (EN MINUTOS) de las actividades desarrolladas el día de hoy y luego PRESIONE EL BOTON REGISTRAR SALIDA
									</v-alert>

									<v-alert
										v-if="metas != null && metas.length == 0"
										:value="true"
										color="warning"
										icon="mdi-alert-decagram"
										dark
										>
										Ud. no ha programado sus metas para el presente mes, por lo que no se considerará ninguna actividad para su informe de cumplimiento de labor efectiva.
									</v-alert>

									<v-row dense v-for="(meta, index) in metas" :key="index" wrap class="ml-2 mr-2"  >
										<v-col cols="12" md="8"  class="pa-0 ma-0" >{{meta.actividad}}</v-col>
										<v-col cols="12" md="2" class="pa-0 ma-0" >
											<v-text-field v-model="meta.cantidad" dense type="number" min="0" class="pa-0 ma-0" 
												:disabled="meta.b_sij*1 == 1"
												placeholder="Cantidad"
											></v-text-field>
										</v-col>
										<v-col cols="12" md="2" class="pa-0 ma-0" v-if="enable_sal_remoto2 == false && remoto2_ini == '' && enable_sal_metas == false && tipo_asistencia*1 == 2">
											<v-text-field v-model="meta.tiempo" dense type="number" min="0" class="pa-0 ma-0" 
												placeholder="Tiempo min." :disabled="meta.cantidad == 0"
											></v-text-field>
										</v-col>
										

									</v-row>
									<v-row>
										<v-divider></v-divider>
									</v-row>
									<v-row v-if="tipo_asistencia*1 == 2">
										<h2><b>Tiempo total {{tiempo_actividades}} minutos </b></h2>
										<template v-if="tiempo_actividades>60">[{{calcula_hora(tiempo_actividades)}}]</template>
									</v-row>
								</v-list>
								<v-flex xs12 sm12>
									<v-btn block color="primary" dark :disabled="metas != null && metas.length > 0
										 && tiempo_actividades == 0
										" @click="registrarSalidaMetas()">Registrar Salida</v-btn>
								</v-flex>
							</v-container>
						</template>
						<template v-else>
							<v-container grid-list-md>
								<v-alert
									:value="true"
									color="primary"
									icon="mdi-alert-decagram"
									dark
									>
									Agregue 1 o MÁS actividades y/o tareas realizadas el día de hoy, considerando el tiempo que le tomo en MINUTOS
								</v-alert>
								<v-layout wrap>
									<v-flex xs10 sm7>
										<v-text-field label="Actividad" maxlength="350" v-model="act1" clearable counter/>
									</v-flex>
									<v-flex xs2 sm3>
										<v-text-field label="Tiempo (EN MINUTOS)" v-model="time1" clearable type="number"
							step="1"/>
									</v-flex>
									<v-flex xs2 sm2>
										<v-btn color="primary" @click="agregarActividad()"><v-icon>mdi-plus-circle</v-icon> Agregar</v-btn> 
									</v-flex>
									<v-flex xs12 sm12>
										<v-data-table
											:headers="headers"
											:items="actividades"
											hide-default-footer
											class="elevation-1"

										>
											<template slot="items" slot-scope="props">
												<td v-for="header in headers" v-bind:key="header.text" @click="select === true ? itemSelected(props.item) :''">
													<template>
														{{props.item[header.value]}}
													</template>
												</td>
												<td class="justify-center px-0">
												
												</td>
											</template>
											<template slot="no-data">
												Agregue las actividades realizadas en el día de hoy y click en el boton + AGREGAR
											</template>
										</v-data-table>
											<h2><b>Tiempo total {{tiempo_actividades}} minutos </b></h2>
											<template v-if="tiempo_actividades>60">[{{calcula_hora(tiempo_actividades)}}]</template>
									</v-flex>
									<v-flex xs12 sm12>
										<v-btn block color="primary" dark :disabled="tiempo_actividades == 0" @click="(enable_sal_remoto2 == true)?registrarComplementarioF():registrarSalidaRemoto()">Registrar Salida</v-btn>
									</v-flex>
								</v-layout>
							</v-container>
						</template>
						
					</v-card-text>
			</v-card>
		</v-dialog>

		<v-dialog v-model="d_anuncio" max-width="900" transition="dialog-transition">
			<v-card>
				 <v-toolbar color="red darken-1" dark>
					<v-toolbar-title>COMUNICADO </v-toolbar-title>
					<v-spacer></v-spacer>
					<v-icon @click="d_anuncio = false">mdi-close</v-icon>
				</v-toolbar>
				<v-card-text>
					<v-img v-if="anuncio.tipo == 'img'"
						:src="'../storage/comunicado/'+anuncio.filename"
					/>
					
					<iframe v-else-if="anuncio.tipo == 'pdf'" :src="'../storage/comunicado/'+anuncio.filename" width="100%" height="600px"/>
					<template v-else-if="anuncio.tipo == 'link'">
						<a :href="anuncio.filename" target="_blank" class="text-decoration-none mt-2">
							<v-alert
								:value="true"
								color="info"
								icon="mdi-alert"
								outlined
								class="headline"
								transition="scale-transition"
							>
								{{anuncio.descripcion}}
								<br/><br/>
								<b>Click aquí para ir al enlace</b>
							</v-alert>
						</a>
					</template>
				</v-card-text>
				<v-divider/>
				<v-card-actions>
					<v-spacer/>
					<v-btn @click="d_anuncio = false" color="error">
						<v-icon>mdi-close</v-icon>
						CERRAR
					</v-btn>
				</v-card-actions>
			</v-card>
						
		</v-dialog>
        <snackbar :snack="snack" />
		<dialogLoader :dialogLoad="dialogLoad"/>

	</v-app>
</template>

<script>
	export default {
   		props:['dataUser'],
      	data() {
         	return {
				menu: 0,
				submenu: -1,
				modulo: 'asistencia',
				drawer:true,
         		fecha:'',
	            ingreso: '',
				salida:'',
				refrg_ini:'',
				refrg_fin:'',
	            enable_asistencia:null,
	            asistenciaID: '',
				d_tipo_asistencia:false,
				tipo_asistencia: '',
				enable_salida:false,
				enable_refrigerio:false,
				enable_refrigerio_r:false,

				enable_ing_remoto2:false,
				enable_sal_remoto2:false,
				enable_sal_metas:'',
				registra_metas_dia:false,
				remoto2_ini:'',
				remoto2_fin:'',
				d_actividades: false,
				d_anuncio:false,
				anuncio:{},
				in_red: null,
				in_remoto: null,
				is_excepcional: null,
				act1:'',
				time1:0,
				headers:[
	               	{text: 'Actividades o tareas', value: 'actividad'},
	              	{text: 'Tiempo en minutos', value: 'tiempo'},
                       
	            ],
	            actividades:[],
				metas:[],
				magistrado:false,
				snack: {
					snackShow:false,
					snackText: '',
					snackColor: '',
				},
				hour:0,
				minutes:0,
				seconds:0,
				dateServer:{},

				enable_ing_remoto:false,
				enable_ing_presencial:false,
				enable_aut_presencial:false,
				consultasSIJ:0,
				dialogLoad:{show:false, text:'', color:''},
				
        	}
     	
     	},
		watch:{
			consultasSIJ(val){
				var metasSIJ = 0
				this.metas.forEach(element => {
					if(element.b_sij*1){
						metasSIJ++;
					}
				});

				if(val == metasSIJ){
		            this.showLoad('', '', false);

					this.d_actividades = true;
					
				} 
			},
			d_actividades(val){
				if(val == false){
					this.showLoad('', '', false);
				}
			}
		},
		computed: {
			tiempo_actividades(){
				var tiempo = 0;
				if(this.metas != null){
					if(this.enable_sal_remoto2 == true && this.remoto2_ini != ''){
						if(this.metas.length > 0){
							tiempo = 180;
						}
					} else if(this.enable_sal_metas == true && this.tipo_asistencia*1 == 1 ){
						tiempo = 480
					} else{
						for (let index = 0; index < this.metas.length; index++) {
							if(tiempo + this.metas[index]['tiempo']*1 > 480){
								this.metas[index]['tiempo'] = 0
								this.showSnack('warning', 'El tiempo total de las actividades remotas no puede exceder los 480 minutos')
							}

							tiempo += this.metas[index]['tiempo']*1
						}
					}
				} else{
					for (let index = 0; index < this.actividades.length; index++) {
						
						tiempo += this.actividades[index]['tiempo']*1
					}
				}
				
				return tiempo;
			},
			getHora(){
				return this.hour + ':' + this.minutes;
			}
		},
     	created(){
			this.getAnuncio();
        	this.iniciarDia();
			this.actualizarTiempo();
			this.iniciarTiempo();
	    },
	    methods: {
			iniciarTiempo(){
				var date = new Date(Date.now());
				this.hour = date.getHours();
				this.minutes = date.getMinutes();
				this.minutes = this.minutes > 9? this.minutes: '0'+(this.minutes.toString());
			},
			actualizarTiempo(){
				setInterval(function(){
					var date = new Date(Date.now());
					this.hour = date.getHours();
					this.minutes = date.getMinutes();
					this.seconds = date.getSeconds();
					this.minutes = this.minutes > 9? this.minutes: '0'+(this.minutes.toString());
					this.seconds = this.seconds > 9? this.seconds: '0'+(this.seconds.toString());
				}.bind(this), 10000);
			},
			getAnuncio(){
				var url = 'asistenciaApi?anuncio=true'
	            axios.get(url)
	                .then(response =>{
						this.anuncio = response.data.anuncio;
						this.d_anuncio =  this.anuncio.activo*1;
					})
					.catch(errors =>{});
			},
	        iniciarDia(){
	            var url = 'asistenciaApi?init=true'
	            axios.get(url)
	                .then(response =>{
						this.fecha = response.data.fecha;
						this.dateServer = response.data.time;
	                    if(response.data.programa_hoy.length != 0){
	                    	this.enable_asistencia = true;
							this.in_red = response.data.in_red
							this.in_remoto = response.data.in_remoto
							this.is_excepcional = response.data.is_excepcional
							this.enable_ing_remoto = response.data.ing_remoto_disponible
							this.enable_ing_presencial = response.data.ing_presencial_disponible
							this.enable_aut_presencial = response.data.ing_autorizacion_disponible
							this.enable_ing_remoto2 = response.data.ini_remoto2_disponible
							this.enable_sal_remoto2 = response.data.fin_remoto2_disponible
							this.enable_sal_metas = response.data.salidaf_disponible
							this.magistrado = response.data.magistrado;
							this.metas = response.data.metas;
							this.registra_metas_dia = response.data.registra_metas;

	                    	if(response.data.asistencia.length != 0){
		                        this.ingreso = response.data.asistencia[0].hora_inicio
		                        this.tipo_asistencia = response.data.asistencia[0].tipo
		                        this.enable_salida = response.data.salida_disponible
		                        this.enable_refrigerio = response.data.refrigerio_disponible
		                        this.enable_refrigerio_r = response.data.refrigerio_r_disponible
		                        if(response.data.asistencia[0].hora_fin != null){
		                            this.salida = response.data.asistencia[0].hora_fin
								} else{
		                            this.salida = ''
								}
								if(response.data.asistencia[0].refrigerio_inicio != null){
		                            this.refrg_ini = response.data.asistencia[0].refrigerio_inicio
								}
								if(response.data.asistencia[0].refrigerio_fin != null){
		                            this.refrg_fin = response.data.asistencia[0].refrigerio_fin
		                        }
								if(response.data.asistencia[0].hora_inicio2 != null){
		                            this.remoto2_ini = response.data.asistencia[0].hora_inicio2
		                        } else{
		                            this.remoto2_ini = ''
								}
								if(response.data.asistencia[0].hora_fin2 != null){
		                            this.remoto2_fin = response.data.asistencia[0].hora_fin2
		                        }
		                        this.asistenciaID = response.data.asistencia[0].id
	                    	} else {
		                        this.ingreso = '';
							}
	                    } else{
	                    	this.enable_asistencia = false;
	                    }

	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.error == 'Unauthenticated'){
	                             window.location = "../login";
	                        } else{
	                             window.location = "../login";
							}

	                    } else if (errors.response.status === 403) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else if (errors.response.status === 419) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else{
	                        this.iniciarDia();
	                    }
	                });
	        },
			registrarIngreso_p1(){
				this.iniciarDia();
				this.d_tipo_asistencia = true;
			},
	        registrarIngreso(tipo){
				this.showLoad('primary', 'Registrando');	
	            axios.post('asistenciaApi?tipo='+tipo)
	            .then(response =>{
	                    if (response.data['statusBD']) {
	                        this.showSnack('success', response.data['messageDB'])
	                        this.ingreso = response.data['hora_ingreso'];
							this.d_tipo_asistencia = false;
	                        this.iniciarDia();
	                    } else{
	                        this.showSnack('red', response.data['messageDB'])
	                    }
						this.showLoad('', '', false);	
	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.message == 'Unauthenticated'){
	                             window.location = "../login";
	                        }
	                    } else if (errors.response.status === 403) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else if (errors.response.status === 419) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else{
		                     this.iniciarDia();
						}
						this.showLoad('', '', false);	

	                });
	        },
			registrarAutIngreso(){
				axios.post('asistenciaApi?autorizacion=true')
	            .then(response =>{
	                    if (response.data['statusBD']) {
	                        this.showSnack('success', response.data['messageDB'])
	                        this.ingreso = response.data['hora_ingreso'];
							this.d_tipo_asistencia = false;
	                        this.iniciarDia();
	                    } else{
	                        this.showSnack('red', response.data['messageDB'])
	                    }
	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.message == 'Unauthenticated'){
	                             window.location = "../login";
	                        }
	                    } else if (errors.response.status === 403) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else if (errors.response.status === 419) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else{
		                     this.iniciarDia();
						}
	                });
			},
	        registrarSalida(){
				if(this.hasRole('Asistencia.UsuarioMetas') && this.enable_sal_metas){
					this.consultarSalida();
				} else{
					this.showLoad('primary', 'Registrando');	
					axios.put('asistenciaApi/'+this.asistenciaID)
	                .then(response =>{
	                    if (response.data['statusBD']) {
	                        this.showSnack('success', response.data['messageDB'])
	                        this.salida = response.data['hora_fin']
							this.iniciarDia();
	                    } else{
	                        this.showSnack('red', response.data['messageDB'])
	                    }
						this.showLoad('','',false)
	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.error == 'Unauthenticated'){
	                             window.location = "../login";
	                        }
						}
	                    else if (errors.response.status === 403) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else if (errors.response.status === 419) {
	                        console.log(errors.response)
	                             window.location = "../inicio";
	                    } else{
		                     this.iniciarDia();
						};
						this.showLoad('','',false)

	                });
				}
	            
	        },
	        registrarRefrigerio(){
	            axios.put('asistenciaApi/'+this.asistenciaID, {refrigerio_ini:true})
	                .then(response =>{
	                    if (response.data['statusBD']) {
	                        this.showSnack('success', response.data['messageDB'])
	                        this.refrg_ini = response.data['hora_fin']
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
	                     this.iniciarDia();
	                });
			},
			registrarRefrigerioR(){
	            axios.put('asistenciaApi/'+this.asistenciaID, {refrigerio_fin:true})
	                .then(response =>{
	                    if (response.data['statusBD']) {
	                        this.showSnack('success', response.data['messageDB'])
	                        this.refrg_fin = response.data['hora_fin']
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
	                     this.iniciarDia();
	                });
	        },

			consultarSalida(){
				this.consultasSIJ = 0;
				if(this.hasRole('Asistencia.UsuarioMetas')){
					this.showLoad('primary', 'Realizando consulta a la BD del SIJ');
					var url = 'asistenciaApi?getTime=true'

					axios.get(url)
							.then(response =>{
								this.dateServer = response.data.time;
								let last_date = response.data.last_date;
								let last_time = response.data.last_time;
								if(this.metas.length > 0){
									/***** CONSULTAS SI EXISTEN METAS SIJ */
									let n_cons_SIJ = 0
									this.metas.forEach(element => {
										if(element.b_sij*1 == true){
											n_cons_SIJ++;
											element.cantidad = '';
											element.tiempo = '';
										}
									})
									console.log(n_cons_SIJ);
									if(n_cons_SIJ > 0){
										this.metas.forEach(element => {
											if(element.b_sij*1 == true){
												let url = 'SIJgetMetas?url_meta=' + element.url_meta + 
																'&dni_u=' + this.dataUser.usuario.numero_documento + 
																'&f_ini=' + last_date + 
																'&t_ini=' + last_time + 
																'&f_fin=' + this.dateServer['year']+'-'+this.dateServer['mon']+'-'+this.dateServer['mday'] + 
																'&t_fin=' +  this.dateServer['hours']+':'+ this.dateServer['minutes']+':'+this.dateServer['seconds']


												axios.get(url)
												.then(response =>{
													if(response.data.data[0] != null){
														element.cantidad = response.data.data[0]['total']
														element.tiempo = '';
														this.consultasSIJ++
													} else if(response.data.status == 0){
														this.showSnack('warning','No se puede establecer la conexion SIJ, de realizar trabajo remoto verifique su conexión VPN (Forticlient)')
														window.location = "../login";
													}
												})
												.catch(errors =>{
													if (errors.response.status === 401) {
														window.location = "../login";
													}
												});
											} else{
												element.cantidad = '';
												element.tiempo = '';
											}
											
										});
									} else{
										this.d_actividades = true;
									}

									this.metas.forEach(element => {
										if(element.b_sij*1 == true){
											let url = 'SIJgetMetas?url_meta=' + element.url_meta + 
															'&dni_u=' + this.dataUser.usuario.numero_documento + 
															'&f_ini=' + last_date + 
															'&t_ini=' + last_time + 
															'&f_fin=' + this.dateServer['year']+'-'+this.dateServer['mon']+'-'+this.dateServer['mday'] + 
															'&t_fin=' +  this.dateServer['hours']+':'+ this.dateServer['minutes']+':'+this.dateServer['seconds']


											axios.get(url)
											.then(response =>{
												if(response.data.data[0] != null){
													element.cantidad = response.data.data[0]['total']
													element.tiempo = '';
													this.consultasSIJ++
												} else if(response.data.status == 0){
													this.showSnack('warning','No se puede establecer la conexion SIJ, de realizar trabajo remoto verifique su conexión VPN (Forticlient)')
													window.location = "../login";
												}
											})
											.catch(errors =>{
												if (errors.response.status === 401) {
													window.location = "../login";
												}
											});
										} else{
											element.cantidad = '';
											element.tiempo = '';
										}
										
									});
								} else{
									this.showSnack('warning', 'Ud. no ha programado sus metas para el presente mes, por lo que no se registrará ninguna actividad')
									this.d_actividades = true
								}
							})
							.catch(errors =>{
								if (errors.response.status === 401) {
									window.location = "../login";
								}
							});
				} else{
					this.d_actividades = true
				}
			},
			registrarSalidaRemoto(){
				this.showLoad('primary', 'Registrando');	
				axios.put('asistenciaApi/'+this.asistenciaID,  {actividades:this.actividades})
	                .then(response =>{
	                    if (response.data['statusBD']) {
							this.d_actividades = false;
	                        this.showSnack('success', response.data['messageDB'])
	                        this.salida = response.data['hora_fin']
	                    } else{
	                        this.showSnack('red', response.data['messageDB'])
	                    }
						this.showLoad('', '', false)

	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401 || errors.response.status === 403 || errors.response.status === 419) {
	                        console.log(errors.response)
							window.location = "../login";
	                    }
	                     else{
							this.iniciarDia();
							this.showLoad('', '', false)
						}
	                });
			},

			registrarComplementarioI(){
				this.showLoad('primary', 'Registrando');	

	            axios.put('asistenciaApi/'+this.asistenciaID, {remoto2_ini:true})
	                .then(response =>{
	                    if (response.data['statusBD']) {
	                        this.showSnack('success', response.data['messageDB'])
	                        this.remoto2_ini = response.data['remoto2_ini']
							this.iniciarDia();
	                    } else{
	                        this.showSnack('red', response.data['messageDB'])
	                    }
						this.showLoad('', '', false)

	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401) {
	                        console.log(errors.response)
	                        if(errors.response.data.error == 'Unauthenticated'){
	                             window.location = "../login";
	                        }
	                     }
						this.iniciarDia();
						this.showLoad('', '', false)

	                });
			},
			registrarComplementarioF(){
				this.showLoad('primary', 'Registrando');	

	            axios.put('asistenciaApi/'+this.asistenciaID,  {actividades:this.actividades, remoto2_fin:true})
	                .then(response =>{
	                    if (response.data['statusBD']) {
							this.d_actividades = false;
	                        this.showSnack('success', response.data['messageDB'])
		                     this.iniciarDia();
						} else{
	                        this.showSnack('red', response.data['messageDB'])
	                    }
						this.showLoad('', '', false)

	                })
	                .catch(errors =>{
	                    console.log(errors);
	                    if (errors.response.status === 401 || errors.response.status === 403 || errors.response.status === 419) {
	                        console.log(errors.response)
							window.location = "../login";
	                    }
	                     else{
		                     this.iniciarDia();
						}
						this.showLoad('', '', false)

	                });
			},

			registrarSalidaMetas(){
				this.showLoad('primary', 'Registrando');	
				let sendData = true;
				this.metas.forEach(element => {
					if(element.cantidad > 0 && element.tiempo == 0 && this.tipo_asistencia*1 == 2 ){
						this.showSnack('warning', 'Debe de completar el tiempo en todas las actividades que indican una cantidad > a 0')
						sendData = false;
						this.showLoad('', '', false)
					}
				});

				if(sendData){
					axios.put('asistenciaMetas/'+this.asistenciaID,  {actividades:this.metas, remoto2_fin:(this.enable_sal_remoto2 == true && this.remoto2_ini != '')})
						.then(response =>{
							if (response.data['statusBD']) {
								this.d_actividades = false;
								this.showSnack('success', response.data['messageDB'])
								this.iniciarDia();
							} else{
								this.showSnack('red', response.data['messageDB'])
							}
							this.showLoad('', '', false)

						})
						.catch(errors =>{
							console.log(errors);
							if (errors.response.status === 401 || errors.response.status === 403 || errors.response.status === 419) {
								console.log(errors.response)
								window.location = "../login";
							}
							else{
								this.iniciarDia();
							}
							this.showLoad('', '', false)

						});
				}
	            
			},


			agregarActividad(){
				if(this.act1 != '' && this.act1 != null && this.time1 > 0 && this.time1 != null){

					let tiempo_total = this.tiempo_actividades + this.time1*1

					if(this.enable_sal_remoto2 == true && this.remoto2_ini != '' && tiempo_total > 180){
						this.showSnack('warning', 'El tiempo total de las actividades en periodo complementario remoto no puede exceder los 180 minutos')
					} else if(this.enable_salida == true && tiempo_total > 480){
						this.showSnack('warning', 'El tiempo total de las actividades remotas no puede exceder los 480 minutos')
					} else{
						var item = {
							actividad:this.act1,
							tiempo:this.time1*1
						}
						this.actividades.push(item)
						this.act1 = '';
						this.time1 = 0;
					}
					
				}
				
			},
			calcula_hora(minutos){
				return Math.floor(minutos/60) + 'hora(s) y ' + minutos % 60 + ' minuto(s)';
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