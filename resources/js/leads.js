// This file doesn't import CSS but otherwise has the same Inertia setup

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist';
import { i18nVue } from 'laravel-vue-i18n';
import axios from 'axios';

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Set CSRF token
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

createInertiaApp({
  // Important: Look specifically in the Inertia directory
  resolve: name => {
    const pages = import.meta.glob('./Inertia/**/*.vue', { eager: true });
    return pages[`./Inertia/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(i18nVue, {
        lang: props.initialPage.props.locale || 'uz',
        resolve: async lang => {
          const langs = import.meta.glob('../../lang/*.json');
          return await langs[`../../lang/php_${lang}.json`]();
        }
      })
      .mount(el);
  },
});