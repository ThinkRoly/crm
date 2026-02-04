<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.team', route.params.id ? ((route.name == 'Teampreview' ? '查看':'编辑') + '团队(id-'+route.params.id+')') : '新增团队']" />

    <a-card class="general-card" style="padding-top:30px">
      <div class="wrapper">
        <a-form ref="formRef" :model="formData" class="form" :label-col-props="{ span: 4 }" :wrapper-col-props="{ span: 18 }" >
          <a-form-item field="name" label="名称" :rules="[{ required: true, message: '请输入名称' }]" >
            <a-input v-model="formData.name" placeholder="请输入"  :disabled="route.name=='Teampreview'"/>
          </a-form-item>
          <a-form-item field="manager" label="负责人">
            <a-select v-model="formData.manager_id" :options="allUser" placeholder="请输入" allow-clear  :disabled="route.name=='Teampreview'"/>
          </a-form-item>
          <a-form-item field="parent_id" label="上级团队">
            <a-select v-model="formData.parent_id" :options="allTeam" placeholder="请输入" allow-clear  :disabled="route.name=='Teampreview'"/>
          </a-form-item>
          <a-form-item>
            <a-space>
              <a-button type="primary" @click="submitForm"  :disabled="route.name=='Teampreview'"> 保存 </a-button>
              <a-button @click="$router.push({ name: 'Team' })"> 返回 </a-button>
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
  import { editTeamInfo, TeamInfo, getTeamInfo, } from '@/api/system';

  const router = useRouter();
  const route = useRoute();

  const formRef = ref<FormInstance>();
  const { loading, setLoading } = useLoading(false);
  const formData = ref<TeamInfo>({} as TeamInfo);
  const allUser = ref();
  const allTeam = ref();
  const id = ref(route.params.id as string);

  const submitForm = async () => {
    const res = await formRef.value?.validate();
    if (res) {
      return;
    }
    setLoading(true);
    try {
      await editTeamInfo(formData.value);
      router.push({ name: 'Team' });
    } catch (err) {
      // you can report use errorHandler or other
    } finally {
      setLoading(false);
    }
  };

  const fetchData = async () => {
    try {
      const { data } = await getTeamInfo(id.value);
      allUser.value = data.allUserListSelect;
      allTeam.value = data.allTeamListSelect;
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
