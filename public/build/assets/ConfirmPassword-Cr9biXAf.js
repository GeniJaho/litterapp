import{C as m,r as d,d as p,b as o,u as e,w as t,F as u,o as f,h as c,a as r,n as _,f as w,i as g}from"./app-CZNMuXyv.js";import{A as b}from"./AuthenticationCard-DHUNqPNt.js";import{_ as x}from"./AuthenticationCardLogo-CCKuotW1.js";import{_ as y}from"./InputError-B5YukZOt.js";import{_ as v}from"./InputLabel-ChgMnCjb.js";import{_ as C}from"./PrimaryButton-Dv3cuahV.js";import{_ as V}from"./TextInput-BUY0sAWZ.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const k={class:"flex justify-end mt-4"},j={__name:"ConfirmPassword",setup($){const s=m({password:""}),i=d(null),n=()=>{s.post(route("password.confirm"),{onFinish:()=>{s.reset(),i.value.focus()}})};return(h,a)=>(f(),p(u,null,[o(e(c),{title:"Secure Area"}),o(b,null,{logo:t(()=>[o(x)]),default:t(()=>[a[2]||(a[2]=r("div",{class:"mb-4 text-sm text-gray-600 dark:text-gray-400"}," This is a secure area of the application. Please confirm your password before continuing. ",-1)),r("form",{onSubmit:g(n,["prevent"])},[r("div",null,[o(v,{for:"password",value:"Password"}),o(V,{id:"password",ref_key:"passwordInput",ref:i,modelValue:e(s).password,"onUpdate:modelValue":a[0]||(a[0]=l=>e(s).password=l),type:"password",class:"mt-1 block w-full",required:"",autocomplete:"current-password",autofocus:""},null,8,["modelValue"]),o(y,{class:"mt-2",message:e(s).errors.password},null,8,["message"])]),r("div",k,[o(C,{class:_(["ml-4",{"opacity-25":e(s).processing}]),disabled:e(s).processing},{default:t(()=>a[1]||(a[1]=[w(" Confirm ")])),_:1},8,["class","disabled"])])],32)]),_:1})],64))}};export{j as default};
