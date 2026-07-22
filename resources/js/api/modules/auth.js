import axios from '../index'

export default {
  login: (data) => axios.post('/auth/login', data),
  logout: () => axios.post('/auth/logout'),
  me: () => axios.get('/auth/me'),
}
