import axios from '../index'

export default {
  list: (params) => axios.get('/subjects', { params }),
  store: (data) => axios.post('/subjects', data),
  update: (id, data) => axios.put(`/subjects/${id}`, data),
  delete: (id) => axios.delete(`/subjects/${id}`),
}
