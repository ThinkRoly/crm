<template>
  <div class="container baseinfo">
    <a-form
      ref="formRef"
      :model="formData"
      label-align="right"
      auto-label-width
      :disabled="route.name == 'CustomerPreview'"
    >
      <a-space direction="vertical" :size="16">
        <a-row :gutter="10">
          <a-col :span="14">
            <a-card class="general-card">
              <template #title> 个人信息 </template>
              <template #extra>
                <a-button
                  v-if="
                    (route.name == 'CustomerFollow' ||
                      route.name == 'CustomerEdit') &&
                    id !== '' &&
                    id !== undefined
                  "
                  type="primary"
                  size="mini"
                  @click="makecall(id)"
                  >一键呼叫</a-button >
                <a-space
                  v-if="
                    (route.name == 'CustomerFollow' ||
                      route.name == 'CustomerEdit') &&
                    id !== '' &&
                    id !== undefined
                  "
                  style="margin-left: 10px"
                >
                  <a-button
                    v-permission="['CustomerIntro']"
                    type="primary"
                    size="mini"
                    @click="intro(id)"
                    >转介绍</a-button
                  >
                  <a-button
                    v-permission="['CustomerDianping']"
                    type="primary"
                    size="mini"
                    @click="dianping()"
                    >主管点评</a-button
                  >
                  <a-popconfirm
                    v-if="
                      userStore.$state.buttonPermission.includes(
                        'CustomerImportant'
                      )
                    "
                    :content="importButtonContext"
                    ok-text="确认"
                    cancel-text="取消"
                    @ok="important()"
                  >
                    <a-button type="primary" size="mini">{{
                      importButton
                    }}</a-button>
                  </a-popconfirm>
                  <a-popconfirm
                    v-if="
                      userStore.$state.buttonPermission.includes('CustomerLock')
                    "
                    :content="lockButtonContext"
                    ok-text="确认"
                    cancel-text="取消"
                    @ok="lock()"
                  >
                    <a-button type="primary" size="mini">{{
                      lockButton
                    }}</a-button>
                  </a-popconfirm>
                  <a-popconfirm
                    v-if="
                      userStore.$state.buttonPermission.includes(
                        'CustomerGiveup'
                      )
                    "
                    content="确认将客户移入公海么？"
                    ok-text="确认"
                    cancel-text="取消"
                    @ok="giveup()"
                  >
                    <a-button type="primary" size="mini">移入公海</a-button>
                  </a-popconfirm>
                  <a-button
                    v-permission="['CustomerAddNotice']"
                    type="primary"
                    size="mini"
                    @click="addNotice"
                    >添加日程</a-button
                  >
                  <a-button
                    v-permission="['CustomerAddBack']"
                    type="primary"
                    size="mini"
                    @click="addBack"
                    >添加回款</a-button
                  >
                  <a-popconfirm
                    v-if="
                      userStore.$state.buttonPermission.includes(
                        'CustomerLahei'
                      ) && formData.status == '1'
                    "
                    content="确认将客户设为无效？"
                    ok-text="确认"
                    cancel-text="取消"
                    @ok="lahei()"
                  >
                    <a-button type="primary" size="mini">设为无效</a-button>
                  </a-popconfirm>
                </a-space>
              </template>
              <a-row>
                <a-col :span="12">
                  <a-form-item
                    label="客户姓名"
                    field="name"
                    :rules="[{ required: true, message: '请输入姓名' }]"
                  >
                    <a-input
                      v-model="formData.name"
                      placeholder="请输入"
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item
                    label="&nbsp;&nbsp;联系电话"
                    field="mobile"
                    :rules="[{ required: true, message: '请输入联系电话' }]"
                  >
                    <a-input
                      v-model="formData.mobile"
                      placeholder="请输入"
                      class="mobile"
                      :disabled="
                        route.params.id > 0 &&
                        !userStore.$state.buttonPermission.includes(
                          'CustomerEditMobile'
                        )
                      "
                    />
                  </a-form-item>
                </a-col>
              </a-row>
              <a-row>
                <a-col :span="12" :width="100">
                  <a-form-item label="跟进状态">
                    <a-select
                      v-model="formData.follow_status"
                      placeholder="请选择"
                      :options="followStatusOptions"
                      allow-clear
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="12">
                  <a-form-item label="星级评分">
                    <a-select
                      v-model="formData.star"
                      placeholder="请选择"
                      :options="starOptions"
                      allow-clear
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="24">
                  <a-form-item label="跟进备注">
                    <a-textarea
                      v-model="formData.remark"
                      placeholder="请输入"
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="24">
                  快捷备注:
                    <a-link v-for="item in remarks" :key="item" @click="setRemark(item)" style="background:#fff;border:1px solid #ccc; color:rgb(47 143 140);margin: 5px;">{{item}}</a-link>
                </a-col>
              </a-row>
            </a-card>
            <br/>
            <a-card
              class="general-card"
              :body-style="{ 'height': '1205px', 'overflow-y': 'scroll' }"
            >
              <template #title> 跟进记录 </template>
              <a-timeline>
                <a-timeline-item
                  v-for="(item, index) in logList"
                  :key="index"
                  :label="item.day"
                  line-type="dashed"
                >
                  <a-tag size="small" :color="item.color">{{
                    item.type
                  }}</a-tag>
                  <br />
                  <a-typography-text
                    type="secondary"
                    :style="{
                      'fontSize': '14px',
                      'marginTop': '4px',
                      'line-height': '28px',
                    }"
                  >
                    {{ item.remark }}
                  </a-typography-text>
                </a-timeline-item>
              </a-timeline>
            </a-card>
          </a-col>
        <a-col :span="10">
        <a-card class="general-card">
          <template #title> 客户信息 </template>
          <a-row>
            <a-col :span="12">
              <a-form-item label="申请金额">
                <a-input v-model="formData.amount" placeholder="请输入">
                  <template #append>元</template>
                </a-input>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="客户性别">
                <a-select
                  v-model="formData.sex"
                  placeholder="请选择"
                  allow-clear
                >
                  <a-option :value="1">男</a-option>
                  <a-option :value="2">女</a-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="客户年龄">
                <a-input v-model="formData.age" placeholder="请输入">
                  <template #append>岁</template>
                </a-input>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="婚姻状况">
                <a-select
                  v-model="formData.marry"
                  placeholder="请选择"
                  allow-clear
                >
                  <a-option :value="1">未婚</a-option>
                  <a-option :value="2">已婚</a-option>
                  <a-option :value="3">离异</a-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="职业身份">
                <a-select
                  v-model="formData.work"
                  placeholder="请选择"
                  :options="workListOptions"
                  allow-clear
                />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="年收入">
                <a-input v-model="formData.income" placeholder="请输入">
                  <template #append>万元</template>
                </a-input>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="申请城市">
                <a-select
                  v-model="formData.city"
                  :options="cityOptions"
                  placeholder="请选择"
                  allow-clear
                />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="渠道来源">
                <a-input v-model="fromInfo" readonly />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="申请时间">
                <a-input v-model="createTime" readonly />
              </a-form-item>
            </a-col>

            <a-col :span="24">
              <a-form-item label="房产信息">
                <a-radio-group v-model="formData.house" type="button">
                  <a-radio :value="0">未知</a-radio>
                  <a-radio :value="1">无房</a-radio>
                  <a-radio :value="2">本地房</a-radio>
                  <a-radio :value="3">外地房</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="车辆信息">
                <a-radio-group v-model="formData.car" type="button">
                  <a-radio :value="0">未知</a-radio>
                  <a-radio :value="2">有</a-radio>
                  <a-radio :value="1">无</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="保单信息">
                <a-radio-group v-model="formData.policy" type="button">
                  <a-radio :value="0">未知</a-radio>
                  <a-radio :value="2">有</a-radio>
                  <a-radio :value="1">无</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="打卡工资">
                <a-radio-group v-model="formData.wage" type="button">
                  <a-radio :value="0">未知</a-radio>
                  <a-radio :value="2">有</a-radio>
                  <a-radio :value="1">无</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="公积金信息">
                <a-radio-group v-model="formData.funds" type="button">
                  <a-radio :value="0">未知</a-radio>
                  <a-radio :value="2">有</a-radio>
                  <a-radio :value="1">无</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="社保信息">
                <a-radio-group v-model="formData.insurance" type="button">
                  <a-radio :value="0">未知</a-radio>
                  <a-radio :value="2">有</a-radio>
                  <a-radio :value="1">无</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="信用情况">
                <a-radio-group v-model="formData.credit" type="button">
                  <a-radio :value="0">未知</a-radio>
                  <a-radio :value="1">无逾期</a-radio>
                  <a-radio :value="2">有逾期</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="信用详情">
                <a-select
                  v-model="formData.credit_detail"
                  placeholder="请选择"
                  allow-clear
                >
                  <a-option :value="1">无逾期</a-option>
                  <a-option :value="2">历史有逾期</a-option>
                  <a-option :value="3">当前有逾期</a-option>
                  <a-option :value="4">严重逾期</a-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="家人是否知晓">
                <a-radio-group v-model="formData.family" type="button">
                  <a-radio :value="0">不确定</a-radio>
                  <a-radio :value="1">知晓</a-radio>
                  <a-radio :value="2">不知晓</a-radio>
                </a-radio-group>
              </a-form-item>
            </a-col>
            </a-row>
        </a-card>
        </a-col>
        </a-row>
      </a-space>
    </a-form>
    <div class="actions">
      <a-space>
        <a-button @click="router.back()"> 返回 </a-button>
        <a-button
          v-if="
            (activekey == 1 || activekey == undefined) &&
            route.name != 'CustomerPreview'
          "
          type="primary"
          :loading="loading"
          @click="onSubmitClick(true)"
        >
          提交
        </a-button>
        <a-button
          v-if="
            (activekey == 1 || activekey == undefined) &&
            route.name == 'CustomerFollow' &&
            nextId != ''
          "
          type="primary"
          :loading="loading"
          @click="onSubmitClick(false)"
        >
          提交并跟进下一个
        </a-button>
        <a-button
          v-if="
            (activekey == 1 || activekey == undefined) &&
            route.name != 'CustomerPreview' &&
            id !== '' &&
            id !== undefined
          "
          :loading="loading"
          @click="onSubmitClickAndGiveup(true)"
        >
          提交并移入公海
        </a-button>
        <a-button
          v-if="
            (activekey == 1 || activekey == undefined) &&
            route.name != 'CustomerPreview' &&
            id !== '' &&
            id !== undefined
          "
          :loading="loading"
          @click="onSubmitClickAndGiveup(false)"
        >
          提交并移入公海跟进下一个
        </a-button>

      </a-space>
    </div>
    <a-modal v-model:visible="noticeModal" :width="700" :footer="false">
      <template #title> 待办日程 </template>
      <a-space :size="10" direction="vertical">
        <a-button
          size="mini"
          @click="notices.push({ date: '', type: 1, remark: '' })"
          >添加日程</a-button
        >
        <a-space v-for="(item, index) in notices" :key="index" :size="2">
          <a-date-picker
            v-model="item.date"
            show-time
            format="YYYY-MM-DD HH:mm:ss"
            style="width: 200px"
          />
          &nbsp;
          <a-select
            v-model="item.type"
            placeholder="请选择"
            :options="noticeOptions"
            allow-clear
          />
          &nbsp;
          <a-input
            v-model="item.remark"
            placeholder="请输入"
            style="width: 300px"
          />
          &nbsp;
          <icon-close @click="notices.splice(index, 1)" />
        </a-space>
      </a-space>
      <br />
      <br />
      <a-timeline direction="horizontal">
        <a-timeline-item
          v-for="(item, index) in noticeList"
          :key="index"
          :label="item.day"
          line-type="dashed"
        >
          <a-tag size="small" :color="item.color">{{ item.type }}</a-tag>
          <br />
          <a-typography-text
            type="secondary"
            :style="{
              'fontSize': '12px',
              'marginTop': '4px',
              'line-height': '28px',
            }"
          >
            {{ item.remark }}
          </a-typography-text>
        </a-timeline-item>
      </a-timeline>
      <a-divider></a-divider>
      <a-button type="primary" @click="addNotices()">保存</a-button>
    </a-modal>
    <a-modal v-model:visible="backModal" :width="700" :footer="false">
      <template #title> 添加回款 </template>
      <a-form :model="backform">
        <a-form-item field="date" label="放款时间">
          <a-date-picker
            v-model="backform.date"
            show-time
            format="YYYY-MM-DD HH:mm:ss"
          />
        </a-form-item>
        <a-form-item field="amount" label="放款金额">
          <a-input-number
            v-model="backform.amount"
            placeholder="请输入"
            hide-button
            @change="computeRealMoney"
          >
            <template #append>元</template>
          </a-input-number>
        </a-form-item>
        <a-form-item field="agency_fee" label="点位(%)">
          <a-input-number
            v-model="backform.agency_fee"
            placeholder="请输入"
            hide-button
            @change="computeRealMoney"
          >
            <template #append>%</template>
          </a-input-number>
        </a-form-item>
        <a-form-item field="agency_fee" label="实际创收">
          <a-input-number
            v-model="backform.realmoney"
            placeholder=""
            hide-button
          >
            <template #append>元</template>
          </a-input-number>
        </a-form-item>
        <a-form-item field="cost" label="成本费用">
          <a-input-number
            v-model="backform.cost"
            placeholder="请输入"
            hide-button
          >
            <template #append>元</template>
          </a-input-number>
        </a-form-item>
        <a-form-item field="hetong" label="合同编号">
          <a-input
            v-model="backform.hetong"
            placeholder="请输入"
            hide-button
          >
          </a-input>
        </a-form-item>
        <a-form-item field="product_id" label="产品">
          <a-select
            v-model="backform.product_id"
            placeholder="全选择产品"
            hide-button
            :options="productList"
          >
          </a-select>
        </a-form-item>
        <a-form-item field="quanzheng" label="权证">
          <a-select
            v-model="backform.quanzheng"
            :options="quanzhengOptions"
            placeholder="请选择"
            allow-clear
          />
        </a-form-item>
        <a-form-item label="备注">
          <a-textarea v-model="backform.remark" placeholder="请输入" />
        </a-form-item>
        <a-form-item>
          <a-button type="primary" @click="submitBack">保存</a-button>
        </a-form-item>
      </a-form>
    </a-modal>
    <a-modal v-model:visible="dianpingModal" :width="500" :footer="false">
      <template #title> 主管点评 </template>
      <a-form :model="dianpingform">
        <a-form-item label="点评">
          <a-textarea
            v-model="dianpingform.remark"
            placeholder="请输入"
            :auto-size="true"
            style="height: 100px"
          />
        </a-form-item>
        <a-form-item>
          <a-button type="primary" @click="submitDianping">保存</a-button>
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script lang="ts" setup>
  import { PropType , ref, computed } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
    import { useAppStore } from '@/store';
  import { FormInstance } from '@arco-design/web-vue/';
  import {
    editCustomInfo,
    CustomInfo,
    getCustomInfo,
    customImportant,
    customLock,
    customGiveup,
    NoticeInfo,
    getNoticeList,
    submitNotices,
    submitBacks,
    laheiCustom,
    submitDianpings,
    makeCall
  } from '@/api/custom';
  import type { SelectOptionData } from '@arco-design/web-vue/es/select/interface';
  import { Message, Modal } from '@arco-design/web-vue';
  import useLoading from '@/hooks/loading';
  import { setNum } from '@/api/customernum';
  import { Base64 } from 'js-base64';

  const remarks = ref(['电话未接通','资质不符','在忙晚点联系','未接','拒接','加微信','已发短信','听到挂','不需要','秒挂','听完挂','通话中','空号','微信跟进','太远了','不线下','没时间','已放款客户不跟进','有异议客户不建议跟进']);
  const logList = ref();
  const noticeList = ref();
  const userStore = useAppStore();
  const props = defineProps({
    activekey: {
      required: true,
    },
  });
  const createTime = ref('');
  const fromInfo = ref('');
  const noticeModal = ref(false);
  const backModal = ref(false);
  const dianpingModal = ref(false);
  const router = useRouter();
  const route = useRoute();
  const formData = ref<CustomInfo>({} as CustomInfo);
  const formRef = ref<FormInstance>();
  const notices = ref<NoticeInfo[]>([{ type: 1 } as NoticeInfo]);
  const id = ref(route.params.id as string);
  const { loading, setLoading } = useLoading();
  const backform = ref({
    hetong: '',
    amount: '' as unknown as number,
    quanzheng: '',
    date: '',
    product_id: '',
    agency_fee: '' as unknown as number,
    remark: '',
    realmoney: '' as unknown as number,
    cost: '' as unknown as number,
  });
  const dianpingform = ref({ remark: '' });
  const computeRealMoney = () => {
    backform.value.realmoney =
      (backform.value.amount * backform.value.agency_fee) / 100;
  };
  const importButton = computed(() => {
    return formData.value.important === 0 ? '标记重要' : '取消重要';
  });
  const importButtonContext = computed(() => {
    return formData.value.important === 0
      ? '确认将当前客户标记重要么?'
      : '确认取消当前客户的重要标记么?';
  });
  const lockButton = computed(() => {
    return formData.value.lock === 0 ? '锁定客户' : '解锁客户';
  });
  const lockButtonContext = computed(() => {
    return formData.value.lock === 0
      ? '确认锁定当前客户么?'
      : '确认解锁当前客户么?';
  });
  const setRemark = (remark: string) => {
    formData.value.remark = remark
  };
  const moreSelectOption = ref(false);
  const cityOptions = ref<SelectOptionData[]>([]);
  const followStatusOptions = ref<SelectOptionData[]>([]);
  const starOptions = ref<SelectOptionData[]>([]);
  const workListOptions = ref<SelectOptionData[]>([]);
  const noticeOptions = ref<SelectOptionData[]>([]);
  const quanzhengOptions = ref<SelectOptionData[]>([]);
  const productList = ref<SelectOptionData[]>([]);

  const fetchData = async () => {
    try {
      const { data } = await getCustomInfo(id.value);
      if (id.value !== '' && id.value !== undefined) {
        formData.value = data.customInfo;
      }
      formData.value.notices = [];
      workListOptions.value = data.workList;
      followStatusOptions.value = data.followStatusList;
      starOptions.value = data.starList;
      cityOptions.value = data.cityList;
      noticeOptions.value = data.noticesList;
      quanzhengOptions.value = data.quanzhengList;
      productList.value = data.productList;
      logList.value = data.logList;
      createTime.value = data.createTime;
      fromInfo.value = data.fromInfo
      if (id.value !== '' && id.value !== undefined) {
        document.title = [data.customInfo.id, data.customInfo.name].join(' - ');
      } else if (
        route.params.introid !== '' &&
        route.params.introid !== undefined
      ) {
        document.title = ['转介绍', route.params.introname].join(' - ');
      }
    } catch (err) {
      console.log(err);
      //
    } finally {
      //
    }
  };

  fetchData();

  const important = async () => {
    try {
      let importantNew = 0;
      if (formData.value.important === 0) {
        importantNew = 1;
      }
      await customImportant(formData.value.id, importantNew);
      Message.success({
        content: '操作成功',
        duration: 5 * 1000,
      });
      formData.value.important = importantNew;
    } catch (err) {
      console.log(err);
    }
  };

  const lock = async () => {
    try {
      let lockNew = 0;
      if (formData.value.lock === 0) {
        lockNew = 1;
      }
      await customLock(formData.value.id, lockNew);
      Message.success({
        content: '操作成功',
        duration: 5 * 1000,
      });
      formData.value.lock = lockNew;
    } catch (err) {
      console.log(err);
    }
  };

  const giveup = async () => {
    try {
      await customGiveup(formData.value.id);
      Message.success({
        content: '操作成功',
        duration: 5 * 1000,
      });
      router.push({ name: 'CustomerList' });
    } catch (err) {
      console.log(err);
    }
  };

  const addNotice = async () => {
    noticeModal.value = true;
    noticeList.value = [];
    try {
      const { data } = await getNoticeList(formData.value.id);
      noticeList.value = data.list;
    } catch (err) {
      console.log(err);
    }
  };

  const addNotices = async () => {
    try {
      const { data } = await submitNotices(formData.value.id, notices.value);
      noticeModal.value = false;
      Message.success({
        content: '操作成功',
        duration: 5 * 1000,
      });
      notices.value = [];
      notices.value = [{ type: 1 } as NoticeInfo];
    } catch (err) {
      console.log(err);
    }
  };

  const addBack = async () => {
    backModal.value = true;
  };

  const dianping = async () => {
    dianpingModal.value = true;
  };

  const submitBack = async () => {
    try {
      const { data } = await submitBacks(formData.value.id, backform.value);
      backModal.value = false;
      Message.success({
        content: '操作成功',
        duration: 5 * 1000,
      });
      backform.value = {
        hetong: '',
        amount: '' as unknown as number,
        quanzheng: '',
        date: '',
        product_id: '',
        agency_fee: '' as unknown as number,
        remark: '',
        realmoney: '' as unknown as number,
        cost: '' as unknown as number,
      };
    } catch (err) {
      console.log(err);
    }
  };

  const flashLog = async () => {
    const { data } = await getCustomInfo(id.value);
    logList.value = data.logList;
  };
  const submitDianping = async () => {
    try {
      const { data } = await submitDianpings(
        formData.value.id,
        dianpingform.value
      );
      backModal.value = false;
      Message.success({
        content: '操作成功',
        duration: 5 * 1000,
      });
      dianpingform.value.remark = '';
      dianpingModal.value = false;
      flashLog();
    } catch (err) {
      console.log(err);
    }
  };

  const lahei = async () => {
    try {
      const { data } = await laheiCustom(formData.value.id, '0');
      Message.success({
        content: '操作成功',
        duration: 5 * 1000,
      });
      router.push({ name: 'CustomerList' });
    } catch (err) {
      console.log(err);
    }
  };
  const intro = (introid: any) => {
    router.push({
      name: 'CustomerEdit',
      params: { introid, introname: formData.value.name },
    });
  };

  const makecall = async (id1: any) => {
    const { data } = await makeCall(id1);
    console.log(data)
    Message.success({
        content: '手机号已转发至手机，请保持客户端处于打开状态',
        duration: 5 * 1000,
    });
  };
  const nextId = computed(function () {
    const ids = Base64.decode(route.params.allIds as string).split('-');
    let idss = '';
    for (let i = 0; i < ids.length; i += 1) {
      if (ids[i] === route.params.id && i < ids.length - 1) {
        idss = ids[i + 1];
        break;
      }
    }
    return idss;
  });

  const next = () => {
    router.push({
      name: route.name as string,
      params: { id: nextId.value, allIds: route.params.allIds },
    });
  };

  const onSubmitClick = async (isClose: boolean) => {
    try {
      const res = await formRef.value?.validate();
      if (res) {
        return;
      }
      if (route.params.introid !== '' && route.params.introid !== undefined) {
        formData.value.introid = route.params.introid as string;
      }
      const { data } = await editCustomInfo(formData.value);
      formData.value.notices = [];
      Message.success({
        content: '操作成功',
        duration: 2 * 1000,
      });
      setNum();
      if (isClose) {
        // setTimeout(function () {
        //   window.opener = null;
        //   window.open('', '_self');
        //   window.close();
        // }, 500);
      } else {
        next();
      }
    } catch (err) {
      //
    } finally {
      //
    }
  };


  const onSubmitClickAndGiveup = async (isClose: boolean) => {
    try {
      const res = await formRef.value?.validate();
      if (res) {
        return;
      }
      const { data } = await editCustomInfo(formData.value);
      formData.value.notices = [];
      Message.success({
        content: '操作成功',
        duration: 2 * 1000,
      });
      await customGiveup(formData.value.id);
      if (isClose) {
        setTimeout(function () {
           window.opener = null;
           window.open('', '_self');
           window.close();
        }, 500);
      } else {
        next();
      }
    } catch (err) {
      //
    } finally {
      //
    }
  };
</script>

<style scoped lang="less">
  .container {
    padding: 0 20px 40px 20px;
    overflow: hidden;
  }

  .baseinfo .general-card {
    border: 1px solid var(--color-neutral-3);
  }

  .actions {
    position: fixed;
    right: 0;
    bottom: 0;
    left: 0;
    height: 60px;
    padding: 14px 20px 14px 0;
    text-align: center;
    background: var(--color-bg-2);
  }

  .baseinfo .arco-btn-size-mini {
    padding: 0 3px;
  }

</style>

<style>
.arco-input-wrapper .arco-input[disabled]
{
    -webkit-text-fill-color: var(--color-text-2);
}
</style>
