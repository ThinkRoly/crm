<template>
    <div class="container baseinfo">
        <a-space  direction="vertical" :size="4" fill>
        <a-row :gutter="24">
            <a-col :span="3">
                <div>权限</div>
            </a-col>
            <a-col :span="21">
                <a-row class="grid-demo" :gutter="24">
                    <a-col :span="12">
                        数据权限
                    </a-col>
                    <a-col :span="12">
                        每日上限
                    </a-col>
                </a-row>
                <a-row class="grid-demo" :gutter="24">
                    <a-col :span="12">
                        <a-space  direction="vertical" :size="0" style="line-height:30px;">
                        <a-checkbox-group v-model="config1.types">
                        <a-checkbox :disabled="preview" value="public">公共池领取数据</a-checkbox> 
                        <a-checkbox :disabled="preview" value="inner">内部流转分配数据</a-checkbox> 
                        <a-checkbox :disabled="preview" value="new">新数据分配</a-checkbox> 
                        </a-checkbox-group>
                        </a-space>
                    </a-col>
                    <a-col :span="12">
                        <a-space  direction="vertical" :size="4">
                        <a-input v-model="config1.publicLimit" :disabled="preview"></a-input> 
                        <a-input v-model="config1.innerLimit" :disabled="preview"></a-input> 
                        <a-input v-model="config1.newLimit" :disabled="preview"></a-input> 
                        </a-space>
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
        <a-row :gutter="24">
            <a-col :span="3">
                <div>备注</div>
            </a-col>
            <a-col :span="21">
                <a-textarea :disabled="preview" v-model="config1.remark"></a-textarea>
            </a-col>
        </a-row>
        <a-row>
            <a-col :span="24" style="text-align:center">
                <a-divider></a-divider>
                <a-button type="primary" :disabled="preview" @click="submit()">保存</a-button>
            </a-col>
        </a-row>
        </a-space>
    </div>
</template> 

<script lang="ts" setup>
  import { ref, reactive, toRef, toRefs, computed } from 'vue';
  import {
    updateAssignInfo,
  } from '@/api/assign';

  const props = defineProps({
  config: {
    type: Object
  },
  preview: {
    type: Boolean,
    default: true,
  },
  id: {
    default: 0,
  },
  closeModal: {
    type: Function,
    default: () => {}
  }


});

const config1 = computed(() => {return props.config});

const submit = async () => {
    try {
        await updateAssignInfo(props.id, config1.value) ;
        props.closeModal();
    } catch (err) {
        // 
        console.log(err)
    } finally {
        // 
    }
}

</script>