require('./bootstrap');

import Vue from 'vue';
import ElementUI from 'element-ui';
import VueSweetalert2 from 'vue-sweetalert2';

window.Vue = require('vue').default;

import store from './store/index';

const options = {
    icon: 'success',
    timer: 6000
};

Vue.use(ElementUI);
Vue.use(VueSweetalert2, options);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('form-submit', require('./components/LoaderFormSubmit.vue').default);
Vue.component('form-submit-game', require('./components/LoaderFormSubmitGame.vue').default);
Vue.component('registration-form-builder', require('./components/RegistrationFormBuilder.vue').default);
Vue.component('registration-form-picker', require('./components/RegistrationFormPicker.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    store
});
