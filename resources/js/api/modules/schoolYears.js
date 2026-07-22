import axios from '../index'

export default {
  list: (params) => axios.get('/school-years', { params }),
  store: (data) => axios.post('/school-years', data),
  update: (id, data) => axios.put(`/school-years/${id}`, data),
  delete: (id) => axios.delete(`/school-years/${id}`),
}
