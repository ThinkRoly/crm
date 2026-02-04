<template>
    <div class="container">
        <Breadcrumb :items="['menu.system.setting']" />
        <a-card class="general-card" style="padding-top:30px">
            <div class="wrapper">
                <a-form ref="formRef" :model="formData" label-align="right" auto-label-width>
                    <a-space direction="vertical" :size="16">
                        <a-card class="general-card">
                            <div class="wrapper">
                                <a-form-item field="ip" label="ip白名单:">
                                    <a-input v-model="formData.ip" allow-clear />
                                </a-form-item>
                                <a-form-item field="ip" label="系统登录时间:">
                                    <a-time-picker type="time-range" style="width:200px;"
                                      v-model="formData.time"
                                      show-time
                                    />，
                                    <a-select v-model="formData.role_id" :options="allRoles" placeholder="请选择" allow-clear multiple style="width:350px;"/>
                                    &nbsp;可24小时登录
                                </a-form-item>
                                <a-form-item field="ip" label="公告:">
                                    <a-textarea v-model="formData.gonggao" allow-clear />
                                </a-form-item>
                                <a-form-item>
                                    <a-button type="primary" @click="submitForm"> 保存 </a-button>
                                </a-form-item>
                                <a-form-item field="ip" label="排重包下载:">
                                    <a href="/api/system/setting?download=md5">下载链接</a>
                                  </a-form-item>
                            </div>
                        </a-card>
                    </a-space>
                </a-form>
            </div>
        </a-card>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import useLoading from '@/hooks/loading';
import { FormInstance } from '@arco-design/web-vue';
import { getSetting, setSetting} from '@/api/user';
import { Message } from '@arco-design/web-vue';


const formRef = ref<FormInstance>({} as FormInstance);
const {  setLoading } = useLoading(false);
const formData = ref({'ip':'', 'gonggao':'', 'time':[], 'role_id':[]});
const allRoles = ref();

const submitForm = async () => {
    setLoading(true);
    try {
        await setSetting(formData.value)
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
        const { data } = await getSetting();
        formData.value = data.setting;
        allRoles.value = data.roles;
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
