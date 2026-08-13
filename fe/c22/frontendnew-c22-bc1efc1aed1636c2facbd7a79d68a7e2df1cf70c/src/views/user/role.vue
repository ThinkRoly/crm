<template>
  <div class="container">
    <Breadcrumb
      :items="['menu.system', 'menu.system.user', 'menu.system.user.role']"
    />
    <a-row style="margin-bottom: 16px">
      <a-col :span="24">
        <a-card class="general-card" style="padding-top:30px">
          <div class="wrapper">
            <a-form :model="formData" class="form" :label-col-props="{ span: 4 }" :wrapper-col-props="{ span: 18 }" >
              <a-form-item field="roles" label="角色">
                <a-select v-model="formData.roles" :options="allRoles" placeholder="请选择" allow-clear multiple />
              </a-form-item>
              <a-form-item>
                <a-space>
                  <a-button type="primary" @click="submitForm"> 保存 </a-button>
                  <a-button @click="$router.push({ name: 'User' })"> 返回 </a-button>
                </a-space>
              </a-form-item>
            </a-form>
          </div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script lang="ts" setup>
  import { ref } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { getUserInfo, updateUserRole, UserInfo } from '@/api/system';

  const route = useRoute();
  const router = useRouter();

  const id = ref(route.params.id as string)
  const userName = ref();
  const formData = ref<UserInfo>({} as UserInfo);
  const allRoles = ref();

  const submitForm = async () => {
    try {
      const { data } = await updateUserRole(
        id.value,
        formData.value.roles
      );
      router.push({ name: 'User' });
    } catch (err) {
      //
    } finally {
      //
    }
  };

  const fetchData = async () => {
    try {
      const { data } = await getUserInfo(id.value);
      userName.value = data.userInfo.name;
      formData.value.roles = data.userInfo.roles;
      allRoles.value = data.roleList;
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
    name: 'Setting',
  };
</script>

<style scoped lang="less">
  .container { padding: 0 20px 20px 20px; }

  .wrapper { min-height: 580px; padding: 20px 0 0 20px; background-color: var(--color-bg-2); border-radius: 4px; }

</style>
