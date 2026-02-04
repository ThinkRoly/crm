<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.product', route.params.id ? ((route.name == 'ProductView' ? '查看':'编辑') + '产品(id-'+route.params.id+')') : '新增产品']" />

    <a-card class="general-card" style="padding-top:30px">
      <div class="wrapper">
        <a-form ref="formRef" :model="formData" class="form" :label-col-props="{ span: 4 }" :wrapper-col-props="{ span: 18 }" >
          <a-form-item field="name" label="名称" :rules="[{ required: true, message: '请输入名称' }]" >
            <a-input v-model="formData.name" placeholder="请输入"  :disabled="route.name=='ProductView'"/>
          </a-form-item>
          <a-form-item field="bank" label="机构">
            <a-select v-model="formData.bank" :options="allUser" allow-create placeholder="请输入" allow-clear  :disabled="route.name=='ProductView'"/>
          </a-form-item>
          <a-form-item field="amount" label="额度">
              <a-input-number v-model="formData.amount1" placeholder="请输入" allow-clear  :disabled="route.name=='ProductView'" />
                 -
              <a-input-number v-model="formData.amount2" placeholder="请输入" allow-clear  :disabled="route.name=='ProductView'"  />
          </a-form-item>
          <a-form-item field="remark" label="产品描述">
            <a-textarea v-model="formData.remark" placeholder="请输入" allow-clear  :disabled="route.name=='ProductView'"/>
          </a-form-item>
          <a-form-item>
            <a-space>
              <a-button type="primary" @click="submitForm"  :disabled="route.name=='ProductView'"> 保存 </a-button>
              <a-button @click="$router.push({ name: 'Product' })"> 返回 </a-button>
            </a-space>
          </a-form-item>
        </a-form>
      </div>
    </a-card>
  </div>
</template>


<script lang="ts" setup>
  import { ref } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import useLoading from '@/hooks/loading';
  import { FormInstance } from '@arco-design/web-vue';
  import { editProductInfo, getProductInfo, } from '@/api/system';

  const router = useRouter();
  const route = useRoute();

  const formRef = ref<FormInstance>();
  const { loading, setLoading } = useLoading(false);
  const formData = ref({name:'',bank:'',amount2:'' as unknown as number,amount1:'' as unknown as number, 'remark':''});
  const allUser = ref();
  const id = ref(route.params.id as string);

  const submitForm = async () => {
    const res = await formRef.value?.validate();
    if (res) {
      return;
    }
    setLoading(true);
    try {
      await editProductInfo(formData.value);
      router.push({ name: 'Product' });
    } catch (err) {
      // you can report use errorHandler or other
    } finally {
      setLoading(false);
    }
  };

  const fetchData = async () => {
    try {
      const { data } = await getProductInfo(id.value);
      allUser.value = data.allUserListSelect;
      if (id.value !== "" && id.value !== undefined) {
        formData.value = data.teamInfo;
      }
    } catch (err) {
      //
    } finally {
      //
    }
  };

  fetchData();
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
</style>
