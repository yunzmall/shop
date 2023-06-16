<style>
  .overlay {
    position: fixed;
    z-index: 1000;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    background-color: rgba(0, 0, 0, 0.7);
  }

  .overlay .overlay-img-box {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60vw;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #ffffff;
    border-radius: 5px;
  }

  .overlay .overlay-title {
    font-weight: 700;
    color: #000000;
    font-size: 24px;
    padding: 15px;
    width: 100%;
    text-align: center;
    border-bottom: 1px solid #EFEFEF;
  }

  .overlay .overlay-main {
    width: 100%;
    padding: 15px 30px;
    height: 80vh;
    text-align: left;
    overflow-y: scroll;
  }

  .overlay .overlay-main img {
    max-width: 90%;
  }

  .overlay .title-box {
    display: flex;
    flex-wrap: wrap;
  }

  .overlay .title-item {
    width: 50%;
  }

  .overlay .title-bg {
    width: 170px;
    line-height: 30px;
    text-align: center;
    background-color: #196dfa;
    color: #ffffff;
    box-shadow: 2px 2px 6px 0px rgba(188, 195, 206, 0.8);
  }

  .overlay .title-p {
    margin-top: 10px;
    color: #000000;
  }

  .overlay .title {
    color: #000000;
    font-size: 18px;
    margin: 50px 0 20px 0;
  }

  .overlay .overlay-foot {
    cursor: pointer;
    padding: 15px;
    width: 100%;
    text-align: center;
    background-color: #196dfa;
    color: #ffffff;
  }
</style>
<template id="introduce">
  <div class="overlay" @click="closeOverlay">
    <div class="overlay-img-box" @click.stop>
      <div class="overlay-title">关于图片智能在线设计</div>
      <div class="overlay-main">
        <p>企业在内容营销过程中会面临哪些设计难题</p>
        <div class="title-box">
          <div class="title-item">
            <div class="title-bg">1、设计素材稀缺</div>
            <p class="title-p">素材不够，寻找周期长，版权设计得不到保护，随时面临风险</p>
          </div>
          <div class="title-item">
            <div class="title-bg">2、设计效率跟不上需求</div>
            <p class="title-p">热点爆发速度快，来不及设计</p>
          </div>
          <div class="title-item">
            <div class="title-bg">3、品牌形象难统一</div>
            <p class="title-p">品牌一致性难以保证，影响消费者对品牌的识别与判断</p>
          </div>
          <div class="title-item">
            <div class="title-bg">4、创意资产管理难度高</div>
            <p class="title-p">高昂成本制作的宣传物料，不能得到合理使用，管理混乱...</p>
          </div>
        </div>
        <p style="color: #196dfa;font-weight: 600;margin: 20px 0;">如果你的企业在做内容营销过程中，内容物料设计还让你头疼，让你烦恼。来看看创客贴如何成为你企业营销内容
          设计的好帮手</p>
        <div style="text-align: center;color: #000000;font-weight: 600;">
          <p class="title">1.助力企业创意内容的高效生产</p>
          <p>完成设计 只需三步</p>
          <img :src="tips2" alt="">
          <p class="title">2.全场景营销覆盖</p>
          <img :src="tips1" alt="">
          <p style="margin-top: 20px;">8000万+可商用高清版权图片</p>
          <p>30万+精品原创设计模板</p>
          <p>3万+原创插画组合元素</p>
          <p>1000+款版权商用字体</p>
          <p>300+设计场景全行也覆盖</p>
          <p>诸多版权内容资源，满足各种设计需求</p>

          <p class="title">3.授权范围</p>
          <img :src="tips3" alt="">
        </div>
      </div>
      <template v-if="!app_id">
        <div class="overlay-foot" style="display: flex;justify-content: space-between;" v-if="is_auth">
          <span> 您未配置图片智能在线设计授权信息，请先到装修DIY-基础设置中进行配置！</span> <span style="font-size: 12px;line-height: 22px;" @click="jumpUrl">点击此处去配置></span>
        </div>
        <div class="overlay-foot" v-if="!is_auth">
          该功能需要按年付费授权，如需开通请联系客服！
        </div>
      </template>
    </div>
  </div>
</template>
<script>
  //图片智能在线设计的图片
  const tip1png = "{{static_url('../resources/views/goods/assets/images/tip1.png')}}";
  const tip2png = "{{static_url('../resources/views/goods/assets/images/tip2.png')}}";
  const tip3png = "{{static_url('../resources/views/goods/assets/images/tip3.png')}}";
  Vue.component("introduce", {
    template: "#introduce",
    props: {
      is_auth: {
        type: Boolean,
        default: false
      },
      app_id: {
        type: String,
        default: ""
      },
    },
    model: {
      prop: 'text',
      event: 'closeOverlay',
    },
    data() {
      return {
        tips1: tip1png,
        tips2: tip2png,
        tips3: tip3png,
      }
    },
    methods: {
      closeOverlay() {
        this.$emit('closeOverlay', false);
      },
      jumpUrl() {
        let url = window.location.href.substring(0, window.location.href.indexOf('route='));
        window.location.href = url + 'route=plugin.decorate.admin.decorate-set.set-info';
      },
    }
  })
</script>