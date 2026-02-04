import axios from 'axios';
import { ref } from 'vue';

export const customNum = ref(0);
export const approveNum = ref(0);
export async function setNum() {
  const  { data }  = await axios.get('/api/custom/follownum', { });
  customNum.value = data.custom_num;
  approveNum.value = data.approve_num;
}

