<template>
  <div class="container">
    <Breadcrumb :items="['menu.operate', 'menu.operate.channel', route.params.id ? ((route.name == 'ChannelView' ? '查看':'编辑') + '渠道(id-'+route.params.id+')') : '新增渠道']" />

    <a-card class="general-card" style="padding-top:30px">
      <div class="wrapper">
        <a-form ref="formRef" :model="formData" class="form" :label-col-props="{ span: 4 }" :wrapper-col-props="{ span: 18 }" >
          <a-form-item field="name" label="名称" :rules="[{ required: true, message: '请输入名称' }]" >
            <a-input v-model="formData.name" placeholder="请输入"  :disabled="route.name=='ChannelView'"/>
          </a-form-item>
          <a-form-item field="show_name" label="展示名称" :rules="[{ required: true, message: '请输入展示名称' }]" >
            <a-input v-model="formData.show_name" placeholder="请输入"  :disabled="route.name=='ChannelView'"/>
          </a-form-item>
          <a-form-item field="type" label="类型">
            <a-select v-model="formData.type" :options="allTeam" placeholder="请选择" allow-clear  :disabled="route.name=='ChannelView'"/>
          </a-form-item>
          <a-form-item field="cost" label="成本">
            <a-input-number v-model="formData.cost" placeholder="请输入"  :disabled="route.name=='ChannelView'"/>
          </a-form-item>
          <a-form-item field="en_name" label="代码配置">
            <a-input v-model="formData.en_name" placeholder="请输入"  :disabled="route.name=='ChannelView'"/>
          </a-form-item>
          <a-form-item label="配置1">
              <a-textarea v-model="formData.config"  :disabled="route.name=='ChannelView'"/>
          </a-form-item>
          <a-form-item label="配置2">
              <a-textarea v-model="formData.token" :disabled="route.name=='ChannelView'" />
          </a-form-item>
          <a-form-item label="备注">
              <a-textarea v-model="formData.remark" :disabled="route.name=='ChannelView'" />
          </a-form-item>
          <a-form-item>
            <a-space>
              <a-button type="primary" @click="submitForm" v-if="route.name!='Userpreview'"> 保存 </a-button>
              <a-button @click="$router.push({ name: 'Channel' })" > 返回 </a-button>
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
  import { editChannelInfo, getChannelInfo } from '@/api/channel';

  const router = useRouter();
  const route = useRoute();

  const formRef = ref<FormInstance>();
  const { setLoading } = useLoading(false);
  const formData = ref({'name':'', 'show_name':'', cost:'' as unknown as number, config:'', token:'', remark:'',type:'1', 'en_name':''});
  const allTeam = ref([ { label: '渠道接入CRM', value: '1', }, { label: 'CRM接入渠道', value: '2', }]);
  const id = ref(route.params.id as string);

  const submitForm = async () => {
    const res = await formRef.value?.validate();
    if (res) {
      return;
    }
    setLoading(true);
    try {
      await editChannelInfo(formData.value);
      router.push({ name: 'Channel' });
    } catch (err) {
      // you can report use errorHandler or other
    } finally {
      setLoading(false);
    }
  };

  const fetchData = async () => {
    try {
      const { data } = await getChannelInfo(id.value);
      if (id.value !== "" && id.value !== undefined) {
        formData.value = data.data;
      }
    } catch (err) {
      //
    } finally {
      //
    }
  };

  fetchData();
</script>

<script lang="ts">
  export default {
    name: 'Step',
  };
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }
</style>
