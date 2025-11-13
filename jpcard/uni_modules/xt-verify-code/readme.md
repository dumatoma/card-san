# 验证码输入框
## 使用方法
```vue
<xt-verify-code v-model="verifyCode" @confirm="confirm"></xt-verify-code>
```

## 参数
|属性						|类型		|说明																								|默认值	|
|--							|--			|--																									|--			|
|value(v-model)	|string	|值(可双向绑定)																			|''			|
|type						|string	|验证输入框类型可选值: box、middle、bottom					|box		|
|inputType			|string	|输入类型 可选值：text、number											|number	|
|size						|number	|验证码框数量																				|6			|
|isFocus				|boolean|是否一开始聚焦																			|true		|
|isPassword			|boolean|是否使用密码方式显示																|false	|
|cursorColor		|string	|光标颜色																						|#cccccc|
|boxNormalColor	|string	|显示框未选中的颜色																	|#cccccc|
|boxActiveColor	|string	|显示框选中的颜色如果type='middle' 则是线的默认样式	|#000000|
|color					|string	|输入文字的颜色																			|#333333|
|@confirm				|event	|输入完成的回调事件  {(code)=>{}}										|				|

## 常见问题
- <span style="color: red;">如何改变输入框的大小？</span>
1. **通过样式穿透的方法**
```css
	/deep/ .xt__verify-code .xt__input-ground .xt__box {
		width: 40rpx !important;
		height: 60rpx !important;
	}

	/deep/ .xt__input-ground .xt__box .xt__code-text {
		font-size: 28rpx !important;
	}
```

- <span style="color: red;">输入框之间的边距如何改变？</span>
1.  **组件内容是flex布局，可在组件增加一个父级标签，修改父级的宽度或内边距(padding)**