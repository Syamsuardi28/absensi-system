import axios from '../index'

export default {
  summary: () => axios.get('/dashboard/summary'),
}
