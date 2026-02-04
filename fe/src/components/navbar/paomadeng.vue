<template>
    <div class="mobile-marquee">
        <icon-sound v-if="gonggao" style="color:red"/>&nbsp;&nbsp;
        <div class="mobile-marquee-wrapper" ref="wrapper">
            <div class="mobile-marquee-text" ref="text1" :style="{ 'left': textLeft, 'transition': textTransition }">
                {{ gonggao }}
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue';

const textLeft= ref("");
const textTransition= ref("");
const text1 = ref();
const timeer = ref(0);
const props = defineProps({
  gonggao : {
    type: String,
    required: true,
  },
});
// 跑马灯运作
const marquee = () => {
   const times = ref(0);
   const wrapperWidth = 500;
   const textWidth = text1.value.offsetWidth;
   if (textWidth <= wrapperWidth) {
    setTimeout(marquee, 10000);
    return;
   }

    textLeft.value = `${wrapperWidth}px`;


    function textRoll() {
        times.value += 1;
        textLeft.value = `-${times.value*5}px`;
        if (times.value * 5 >= textWidth) {
            clearInterval(timeer.value)
            marquee()
        }
    }


};
setTimeout(marquee, 1000);
</script>

<style>
.mobile-marquee {
    display: flex;
    align-items: center;
    height: 40px;
    margin: 0 16px;
}

.mobile-marquee-wrapper {
    flex: 1;
    height: 40px;
    overflow: hidden;
    position: relative;
    width: 500px;
}

.mobile-marquee img {
    width: 14px;
    height: 12px;
    margin-right: 7px;
}

.mobile-marquee-text {
    color: red;
    white-space: nowrap;
    line-height: 40px;
    position: absolute;
}
</style>
