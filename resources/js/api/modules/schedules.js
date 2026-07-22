import axios from '../index'

export default {
  list: (params) => axios.get('/schedules', { params }),
  store: (data) => axios.post('/schedules', data),
  update: (id, data) => axios.put(`/schedules/${id}`, data),
  delete: (id) => axios.delete(`/schedules/${id}`),
}
