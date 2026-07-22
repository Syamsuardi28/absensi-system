import axios from '../index'

export default {
  list: (params) => axios.get('/students', { params }),
  store: (data) => axios.post('/students', data),
  show: (id) => axios.get(`/students/${id}`),
  update: (id, data) => axios.put(`/students/${id}`, data),
  delete: (id) => axios.delete(`/students/${id}`),
}
