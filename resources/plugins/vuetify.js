// src/plugins/vuetify.js

import Vue from 'vue'
import Vuetify from 'vuetify'
//import { rtdbPlugin } from 'vuefire';

Vue.config.productionTip = false

Vue.use(Vuetify)
//Vue.use(rtdbPlugin);

const opts = {}

export default new Vuetify(opts)