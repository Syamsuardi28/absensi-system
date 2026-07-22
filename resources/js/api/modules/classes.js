import axios from '../index'

export default {
  list: (params) => axios.get('/classes', { params }),
  store: (data) => axios.post('/classes', data),
  show: (id) => axios.get(`/classes/${id}`),
  update: (id, data) => axios.put(`/classes/${id}`, data),
  delete: (id) => axios.delete(`/classes/${id}`),
}
