<style>
  body{background-color: #efefef;}
  [v-cloak] {display: none;}
  .vue-title-yz-modular {
    padding: 10px;
    margin: 20px 0;
    background-color: #fff;
    border-radius: 8px;
  }

  .vue-title-yz-box {
    display: flex;
    margin: 5px 0;
    line-height: 32px;
    font-size: 18px;
    color: #333;
    font-weight: 600;
  }

  .vue-title-yz-green {
    width: 4px;
    height: 18px;
    margin-top: 6px;
    background: #29ba9c;
    display: inline-block;
    margin-right: 10px;
  }

  .vue-title-yz-text {
    flex: 1;
    font-size: 14px;
  }

  .vue-title-yz-text span:nth-child(n+2) {
    color: #999;
    margin-left: 20px;
    font-weight: 400;
    font-size: 14px;
  }
  .vue-page{
    border-radius: 5px;
    width: calc(100% - 266px);
    margin-right: 10px;
    position: fixed;
    bottom: 0;
    right: 0;
    padding: 15px 5% 15px 0;
    background: #fff;
    height: 60px;
    z-index: 999;
    box-shadow: 0 2px 9px rgb(51 51 51 / 10%);
    display: flex;
    justify-content: center;
    align-items: center;
  }
</style>

<script>
  Vue.component("box-item", {
    delimiters: ['[[', ']]'],
    props: {
      text: {
        type: String | Array
      }
    },
    template: `<div class="vue-title-yz-modular">
				<div class="vue-title-yz-box" v-if="textList.length>0">
					<div class="vue-title-yz-green"></div>
					<div class="vue-title-yz-text"><span v-for="(text,i) in textList" :key="i">[[text]]</span></div>
          <slot name="btn"></slot>
				</div>
				<slot></slot>
				</div>`,
    computed: {
      textList() {
        if (Array.isArray(this.text)) return this.text;
        else return [this.text];
      }
    }
  })

  Vue.component("vue-page",{
    delimiters: ['[[', ']]'],
    template:`<div class="vue-page-box">
      <div style="height:60px;"></div>
      <div class="vue-page"><slot></slot></div>
    </div>`
  })
</script>