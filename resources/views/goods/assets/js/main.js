const ComponentPathMap = new Map();
const ComponentNameMap = new Map();

// 商品格式用到的
var old_attr = '' // 每次当属性获得焦点时都会获取输入框内的值，保存于此
var old_attr_value = '' // 每次当属性值获得焦点时都会获取输入框内的值，保存于此
let first_column_rule = [] // 第一列使用相同的合并规则 (不能存在data中)

new Vue({
  el: "#app",
  delimiters: ["[[", "]]"],
  data() {
    return {
      currentShowPage: null,
      pages: {},
      showComponentName: "",
      componentLoaded: false,
      http_url:httpUrl,
      // 防止快速点击保存
      saveStatus:true
    };
  },
  created() {
    let title_hide_dom = document.getElementsByClassName('vue-head');
    title_hide_dom[0].style.display = 'block';
    this.fetchData(GetGoodsDataUrl).then((pageGroup) => {
      const pages = {};
      let loadedComponentCount = 0;
      let componentCount = 0;
      pageGroup.forEach((page) => {
        const childrens = [];
        page.column.forEach((columnItem) => {
          //* 把页面的组名和页面的名称组成一个路径
          let path = columnItem.group + "/" + columnItem.template_code;
          if (!ComponentPathMap.has(columnItem.page_path)) {
            //* 生成一个页面路径别名。如果其他页面也是同样的路径就会使用同样的别名，把别名和页面路径连在起义就是加载页面组件js文件的路径
            const pageAlias = "page" + ComponentPathMap.size;
            ComponentPathMap.set(columnItem.page_path, pageAlias);
            require.config({
              paths: {
                [pageAlias]: columnItem.page_path,
              },
            });
          }
          path = ComponentPathMap.get(columnItem.page_path) + "/" + path;
          const componentName = "Component" + Date.now() + Math.round(Math.random() * 1000); //* 生成组件名称
          ComponentNameMap.set(componentName, path);
          childrens.push({
            attr_hide:columnItem.attr_hide,
            title: columnItem.title,
            name: columnItem.template_code,
            path,
            componentName,
            data: columnItem.data,
            widget_key: columnItem.widget_key,
            group: page.group,
          });
          componentCount++; //* 每加载一个组件就会递增组件数量，用于判断是否组件是否加载完成
          this.loadComponent(componentName).then((res) => {
            loadedComponentCount++;
            //* 当所有组件文件加载完成才会显示页面，因为vue的ref需要组件加载完后才能获取
            if (loadedComponentCount === componentCount) {
              this.componentLoaded = true;
            }
          });
        });
        pages[page.group] = {
          key: page.group,
          title: page.title,
          childrens,
        };
       
      });
      this.pages = pages;
      this.currentShowPage = Object.keys(pages)[0];
    });
  },
  methods: {
    fetchData(URL, requestParams) {
      return new Promise((resolve, reject) => {
        this.$http
          .post(URL, requestParams)
          .then(function (response) {
            return response.json();
          })
          .then(({ result, data, msg }) => {
            if (result == 0) {
              this.$message({
                message: msg,
                type: "error",
              });
              reject({ result, data, msg });
            }
            resolve(data);
          })
          .catch((err) => {
            reject(err);
          });
      });
    },
    loadComponent(name) {
      let conponentPath = ComponentNameMap.get(name);
      if (!conponentPath) conponentPath = name;
      return new Promise((resolve, reject) => {
        const pageLoading = this.$loading({
          target: ".goods-page_main",
          text: "页面加载中",
        });
        require([conponentPath], (options) => {
          // 注册组件
          this.$options.components[name] = options;
          if (options && options.style) {
            const styleEl = document.createElement("style");
            styleEl.innerHTML = options.style;
            document.body.append(styleEl);
          }
          // 每个组件加载
          this.showComponentName = this.pages[Object.keys(this.pages)[0]]["childrens"][0]["componentName"];
          resolve(name, options);
          pageLoading.close();
        }, (err) => {
          pageLoading.close();
          reject(err);
        });
      });
    },
    filterData(data, keys = []) {
      let returnData = {};
      for (const key in data) {
        if (!keys.includes(key)) {
          returnData[key] = data[key];
        }
      }
      return returnData;
    },
    async save() {
      const submitData = {
        goods: {},
        widgets: {},
      };
      let extraSubmitData  = {}
      let isPass = true;
      for (const key in this.pages) {
        const childrens = this.pages[key].childrens;
        for (const page of childrens) {
          const component = this.$refs[page.componentName][0];
          if (!component.validate) {
            throw new Error(page.title + " 子组件必须有validate方法");
          }
          //* 执行页面组件的validate方法，如果返回false不会发送数据给后端，否则会整合页面组件传过来的数据一并发送给后端
          const data = await component.validate();
          if (data === false) {
            isPass = false;
            // 循环组件逐一跳转校验失败的页面
            this.currentShowPage = page.group;
            this.showComponentName = page.componentName;
            console.log(page.componentName);
            console.log("表单验证不通过"); //TODO 后续去掉console log
            break;
          }
          //* 如果页面的widget_key等于goods就是商品的主要数据，否则其他都是插件的数据，放进widget里
          if (page.widget_key == "goods") {
            for (const propertyKey in data) {
              submitData["goods"][propertyKey] = data[propertyKey];
            }
          } else if (submitData["widgets"][page.widget_key]) {
            for (const propertyKey in data) {
              submitData["widgets"][page.widget_key][propertyKey] = data[propertyKey];
            }
          } else {
            submitData["widgets"][page.widget_key] = data;
          }      
          // 获取额外的数据
          if (component.extraDate) {
            // throw new Error(page.title + " 子组件必须有extraDate方法");
            // 额外数据设置
            const extraDate = await component.extraDate();
            if(extraDate){
              extraSubmitData  = {...extraSubmitData,...extraDate}
            }
          }
        }
        if (isPass === false) break;
      }
      if (isPass === false) return;
      console.log(submitData,{...extraSubmitData,...submitData});
      console.log(SaveGoodsDataUrl,"表单验证通过"); //TODO 后续去掉console log
      if(this.saveStatus){
        this.saveGoodsData(SaveGoodsDataUrl,submitData)
      }
    },
    saveGoodsData(url,submitData){
      this.saveStatus = false
      this.$http.post(url, submitData).then(response => {
        console.log(response,'response');
          if (response.data.result) {
              this.$message({type: 'success',message: '成功!'});
              window.location.href = GoodsList;
          } else{
              console.log(response.data.msg);
              this.$message({type: 'error',message: response.data.msg});
          }
          this.saveStatus = true
      }),function(res){
        console.log(res);
        };
      },
      chooseTab(subPageItem) {
        this.showComponentName = subPageItem.componentName;
      },
  },
  computed: {
    subPages() {
      if (!this.currentShowPage) return [];
      return this.pages[this.currentShowPage]["childrens"] || [];
    },
  },
  watch: {
    currentShowPage() {
      if (this.subPages.length > 0) {
        this.showComponentName = this.subPages[0]["componentName"];
      }
    },
  },
});
