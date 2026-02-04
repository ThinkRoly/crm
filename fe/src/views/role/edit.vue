<template>
  <div class="container">
    <Breadcrumb :items="['menu.system', 'menu.system.role', route.params.id ? ((route.name == 'Rolepreview' ? '查看':'编辑') + '角色(id-'+route.params.id+')') : '新增角色']" />

    <a-card class="general-card" style="padding-top:30px">
      <div class="wrapper">
        <a-form ref="formRef" :model="formData" class="form" :label-col-props="{ span: 4 }" :wrapper-col-props="{ span: 18 }" >
          <a-form-item field="name" label="角色名称" :rules="[{ required: true, message: '请输入名称' }]" >
            <a-input v-model="formData.name" placeholder="请输入"  :disabled="route.name=='Rolepreview'"/>
          </a-form-item>
          <a-form-item field="name" label="客户范围"  :rules="[{ required: true, message: '请输入客户范围' }]">
            <a-radio-group type="button" v-model="formData.views" :disabled="route.name=='Rolepreview'"> <a-radio :value="1">所有客户</a-radio> <a-radio :value="2">下级客户</a-radio> </a-radio-group>
          </a-form-item>
          <a-form-item field="fields" label="客户字段" >
          <a-row :gutter="24" :style="{ marginBottom: '20px' }">
            <a-col :span="8">
              <a-card :style="{ width: '100%' }" :header-style="{height:'32px'}">
                <template #title><p style="font-size: 14px;width:200px"><icon-tag /> 线索</p></template>
                <a-checkbox-group direction="vertical" v-model="formData.fields1">
                  <a-checkbox :value="(item.value as string)" v-for="(item, index) in fieldsList1" :key="index" :disabled="route.name=='Rolepreview'">{{item.label}}</a-checkbox>
                </a-checkbox-group>
              </a-card>
            </a-col>
            <a-col :span="8">
              <a-card :style="{ width: '100%' }" :header-style="{height:'32px'}">
                <template #title><p style="font-size: 14px;"><icon-tag /> 客户</p></template>
                <a-checkbox-group direction="vertical" v-model="formData.fields2">
                  <a-checkbox :value="item.value as string" v-for="(item, index) in fieldsList2" :key="index" :disabled="route.name=='Rolepreview'">{{item.label}}</a-checkbox>
                </a-checkbox-group>
              </a-card>
            </a-col>
            <a-col :span="8">
              <a-card :style="{ width: '100%' }" :header-style="{height:'32px'}">
                <template #title><p style="font-size: 14px;"><icon-tag /> 联系人</p></template>
                <a-checkbox-group direction="vertical" v-model="formData.fields3">
                  <a-checkbox :value="item.value as string" v-for="(item, index) in fieldsList3" :key="index" :disabled="route.name=='Rolepreview'">{{item.label}}</a-checkbox>
                </a-checkbox-group>
              </a-card>
            </a-col>
          </a-row>
          </a-form-item>
          <a-form-item field="rights" label="权限配置">
            <a-tree v-if="allRights.length > 0"  :data="allRights" :disabled="route.name=='Rolepreview'" :checkable="true" v-model:half-checked-keys="formData.halfrights" v-model:checked-keys="formData.rights" show-line :default-expand-all="true" :default-expand-checked="true"></a-tree>
          </a-form-item>
          <div class="actions">
                <a-space>
                    <a-button type="primary" @click="submitForm" :disabled="route.name=='Rolepreview'"> 保存 </a-button>
                    <a-button @click="$router.push({ name: 'Role' })"> 返回 </a-button>
                </a-space>
            </div>
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
  import { editRoleInfo, RoleInfo, getRoleInfo, } from '@/api/system';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';

  const router = useRouter();
  const route = useRoute();

  const fieldsList1 = ref<SelectOptionData[]>([]);
  const fieldsList2 = ref<SelectOptionData[]>([]);
  const fieldsList3 = ref<SelectOptionData[]>([]);

  const formRef = ref<FormInstance>();
  const { loading, setLoading } = useLoading(false);
  const formData = ref<RoleInfo>({} as RoleInfo);
  const allRights = ref([]);
  const id = ref(route.params.id as string);

  const submitForm = async () => {
    const res = await formRef.value?.validate();
    if (res) {
      return;
    }
    setLoading(true);
    try {
      console.log(formData.value)
      await editRoleInfo(formData.value as RoleInfo);
      router.push({ name: 'Role' });
    } catch (err) {
      // you can report use errorHandler or other
    } finally {
      setLoading(false);
    }
  };

  const fetchData = async () => {
    try {
      const { data } = await getRoleInfo(id.value);
      if (id.value !== "" && id.value !== undefined) {
        formData.value = data.roleInfo;
      }
      fieldsList1.value = data.selectFieldList1;
      fieldsList2.value = data.selectFieldList2;
      fieldsList3.value = data.selectFieldList3;
      allRights.value = data.selectRightList;
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
  .actions {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: 60px;
    padding: 14px 20px 14px 0;
    background: var(--color-bg-2);
    text-align: center;
}
</style>
