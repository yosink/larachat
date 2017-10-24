import axios from 'axios'
const instance = axios.create({
    baseURL: 'http://test.app/api',
    timeout: 2000
})

export default instance