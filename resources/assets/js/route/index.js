import Vue from 'vue'
import VueRouter from 'vue-router'

import Home from '../components/Chatting'
import Login from '../components/Login'
import NotFound from '../components/NotFound'
Vue.use(VueRouter)
export default new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'Home',
            component: Home
        },
        {
            path: '/login',
            name: 'login',
            component: Login
        },
        {
            path: '*',
            name: 'NotFound',
            component: NotFound
        }
    ]
})