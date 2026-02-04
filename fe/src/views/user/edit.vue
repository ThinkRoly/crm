<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.user', route.params.id ? ((route.name == 'Userpreview' ? '查看':'编辑') + '用户(id-'+route.params.id+')') : '新增用户' ]" />

    <a-card class="general-card" style="padding-top:30px">
      <div class="wrapper">
        <a-form ref="formRef" :model="formData" class="form" :label-col-props="{ span: 4 }" :wrapper-col-props="{ span: 18 }" >
          <a-form-item field="name" label="姓名" :rules="[{ required: true, message: '请输入姓名' }]" >
            <a-input v-model="formData.name" placeholder="请输入"  :disabled="route.name=='Userpreview'"/>
          </a-form-item>
          <a-form-item field="mobile" label="手机号" :rules="[ { required: true, message: '请输入手机号' }, { match: new RegExp('^[0-9]{11}$'), message: '请输入正确的手机号' }, ]" >
            <a-input v-model="formData.mobile" placeholder="请输入"  :disabled="route.name=='Userpreview'"/>
          </a-form-item>
          <a-form-item field="team_id" label="团队">
            <a-select v-model="formData.team_id" :options="allTeam" placeholder="请选择" allow-clear  :disabled="route.name=='Userpreview'" allow-search/>
          </a-form-item>
          <a-form-item field="parent_id" label="上级">
            <a-select v-model="formData.parent_id" :options="allUser" placeholder="请输入" allow-clear  :disabled="route.name=='Userpreview'" allow-search/>
          </a-form-item>
          <a-form-item>
            <a-space>
              <a-button type="primary" @click="submitForm" v-if="route.name!='Userpreview'"> 保存 </a-button>
              <a-button @click="$router.push({ name: 'User' })" > 返回 </a-button>
            </a-space>
          </a-form-item>
          <a-form-item label="">
            <a-tree :data="userTree"  v-model:expanded-keys="allUserId">
              <template #icon>
                <IconUser />
              </template>
            </a-tree>
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
  import { editUserInfo, UserInfo, getUserInfo, } from '@/api/system';

  const router = useRouter();
  const route = useRoute();

  const formRef = ref<FormInstance>();
  const { setLoading } = useLoading(false);
  const formData = ref<UserInfo>({} as UserInfo);
  const allUser = ref();
  const allTeam = ref();
  const allUserId = ref();
  const userTree = ref();
  const id = ref(route.params.id as string);

  const submitForm = async () => {
    const res = await formRef.value?.validate();
    if (res) {
      return;
    }
    setLoading(true);
    try {
      await editUserInfo(formData.value as UserInfo);
      router.push({ name: 'User' });
    } catch (err) {
      // you can report use errorHandler or other
    } finally {
      setLoading(false);
    }
  };

  const fetchData = async () => {
    try {
      const { data } = await getUserInfo(id.value);
      if (id.value !== "" && id.value !== undefined) {
        formData.value = data.userInfo;
      }
      allUser.value = data.allUserListSelect;
      allUserId.value = data.allUserId;
      allTeam.value = data.allTeamListSelect;
      userTree.value = data.userTree;
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
