<template>
    <div class="container">
        <Breadcrumb :items="['menu.system.usersetting']" />
        <a-form ref="formRef" :model="passwordFromData" label-align="right" auto-label-width>
            <a-space direction="vertical" :size="16">
                <a-card class="general-card">
                    <template #title> 基本信息 </template>
                    <div class="wrapper">
                        <a-form-item field="name" label="姓名:">
                            {{ formData.name }}
                        </a-form-item>
                        <a-form-item field="mobile" label="手机号">
                            {{ formData.mobile }}
                        </a-form-item>
                        <a-form-item field="password" label="部门">
                            {{ formData.department }}
                        </a-form-item>
                        <a-form-item field="parent_id" label="上级">
                            {{ formData.leader }}
                        </a-form-item>
                    </div>
                </a-card>
                <a-card class="general-card">
                    <template #title> 修改密码 </template>
                    <div class="wrapper">
                        <a-form-item field="oldpassword" label="旧密码:"
                            :rules="[{ required: true, message: '请输入旧密码' }, { minLength: 6, message: '至少输入六个字符' }]">
                            <a-input-password v-model="passwordFromData.oldpassword" allow-clear />
                        </a-form-item>
                        <a-form-item field="newpassword" label="新密码"
                            :rules="[{ required: true, message: '请输入新密码' }, { minLength: 6, message: '至少输入六个字符' }]">
                            <a-input-password v-model="passwordFromData.newpassword" allow-clear />
                        </a-form-item>
                        <a-form-item field="newpasswordConfrim" label="确认新密码"
                            :rules="[{ required: true, message: '请输入确认密码' }, { minLength: 6, message: '至少输入六个字符' }]">
                            <a-input-password v-model="passwordFromData.newpasswordConfrim" allow-clear />
                        </a-form-item>
                        <a-form-item>
                            <a-button type="primary" @click="submitForm"> 保存 </a-button>
                        </a-form-item>
                    </div>
                </a-card>
            </a-space>
        </a-form>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import useLoading from '@/hooks/loading';
import { FormInstance } from '@arco-design/web-vue';
import { getUserInfo, resetPwdData, resetPwd } from '@/api/user';
import { UserState } from '@/store/modules/user/types';
import { Message, Modal } from '@arco-design/web-vue';

const router = useRouter();
const route = useRoute();

const formRef = ref<FormInstance>({} as FormInstance);
const { loading, setLoading } = useLoading(false);
const formData = ref<UserState>({} as UserState);
const passwordFromData = ref<resetPwdData>({} as resetPwdData)

const submitForm = async () => {
    const res = await formRef.value?.validate();
    if (res) {
        return;
    }
    if (passwordFromData.value.newpassword !== passwordFromData.value.newpasswordConfrim) {
        formRef.value.setFields({
            newpassword : {
                status: 'error',
                message: '两次输入的密码不一致'
            },
        });
        return
    }
    setLoading(true);
    try {
        const { data } = await resetPwd(passwordFromData.value)
        Message.success({
            content: '操作成功',
            duration: 5 * 1000,
        })
    } catch (err) {
        // you can report use errorHandler or other
    } finally {
        setLoading(false);
    }
};

const fetchData = async () => {
    try {
        const { data } = await getUserInfo();
        formData.value = data;
    } catch (err) {
        //
    } finally {
        //
    }
};

fetchData();
</script>


<style scoped lang="less">
.container {
    padding: 0 20px 20px 20px;
}
</style>
