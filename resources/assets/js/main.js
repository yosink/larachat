import Vue from 'vue'

import moment from 'moment';
import Vuex from 'vuex'
Vue.use(Vuex)

// 本地化，中文时间显示
moment.locale('zh-cn');

Vue.prototype.moment = moment;

Vue.prototype.random = n => Math.floor(n * Math.random());

const store = new Vuex.Store({
    state: {
        name: 'me',
        avatarUrl: `http://omratag7g.bkt.clouddn.com/icon-avatar${Vue.prototype.random(21)}.svg`,
        addr: '未知',
        isShowAbout: false
    },

    mutations: {
        changeName(state, name) {
            state.name = name;
        },
        setAddr(state, addr) {
            state.addr = addr;
        },
        showAbout(state, flag) {
            state.isShowAbout = flag;
        }
    }
})
import App from './App'
import router from './route'
import Cookies from 'js-cookie'
router.beforeEach((to, from,next) => {
    if (to.path !== '/login') {
        if(! Cookies.get('name')){
            next('/login')
        }else{
            next()
        }
    }
    next()
})

const app = new Vue({
    el: '#app',
    router,
    store,
    components: { App },
    template: '<App/>'
});
