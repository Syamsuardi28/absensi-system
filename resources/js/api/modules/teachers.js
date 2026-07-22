import axios from '../index'

export default {
  list: (params) => axios.get('/teachers', { params }),
  store: (data) => axios.post('/teachers', data),
  show: (id) => axios.get(`/teachers/${id}`),
  update: (id, data) => axios.put(`/teachers/${id}`, data),
  delete: (id) => axios.delete(`/teachers/${id}`),
}
