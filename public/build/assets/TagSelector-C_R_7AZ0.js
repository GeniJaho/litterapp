import{_ as r}from"./PrimaryButton-Dv3cuahV.js";import{_ as c}from"./TagBox-D1H7U3OL.js";import{r as f,d as k,a as l,b as a,w as g,o as S,f as w}from"./app-CZNMuXyv.js";const B={class:"mt-8 space-y-2"},R={class:"flex"},U={class:"ml-4 mt-0.5"},z={class:"flex"},E={class:"ml-4 mt-0.5"},h={class:"flex"},M={class:"ml-4 mt-0.5"},N={class:"flex"},$={class:"ml-4 mt-0.5"},j={class:"flex"},y={class:"ml-4 mt-0.5"},O={class:"flex"},q={class:"ml-4 mt-0.5"},H={__name:"TagSelector",props:{tags:Object},emits:["tag-selected"],setup(s,{emit:p}){const d=p,o=f(null),u=f(null),n=f(null),i=f(null),m=f(null),v=f(null),b=()=>{o.value&&(d("tag-selected",o.value),o.value=null)},V=()=>{u.value&&(d("tag-selected",u.value),u.value=null)},T=()=>{n.value&&(d("tag-selected",n.value),n.value=null)},x=()=>{i.value&&(d("tag-selected",i.value),i.value=null)},C=()=>{m.value&&(d("tag-selected",m.value),m.value=null)},_=()=>{v.value&&(d("tag-selected",v.value),v.value=null)};return(A,e)=>(S(),k("div",B,[l("div",R,[a(c,{class:"w-full md:w-96",items:s.tags.material,nullable:!0,placeholder:"Material",modelValue:o.value,"onUpdate:modelValue":e[0]||(e[0]=t=>o.value=t)},null,8,["items","modelValue"]),l("div",U,[a(r,{class:"whitespace-nowrap",disabled:!o.value,onClick:b},{default:g(()=>e[6]||(e[6]=[w(" Remove ")])),_:1},8,["disabled"])])]),l("div",z,[a(c,{class:"w-full md:w-96",items:s.tags.brand,nullable:!0,placeholder:"Brand",modelValue:u.value,"onUpdate:modelValue":e[1]||(e[1]=t=>u.value=t)},null,8,["items","modelValue"]),l("div",E,[a(r,{class:"whitespace-nowrap",disabled:!u.value,onClick:V},{default:g(()=>e[7]||(e[7]=[w(" Remove ")])),_:1},8,["disabled"])])]),l("div",h,[a(c,{class:"w-full md:w-96",items:s.tags.content,nullable:!0,placeholder:"Content",modelValue:m.value,"onUpdate:modelValue":e[2]||(e[2]=t=>m.value=t)},null,8,["items","modelValue"]),l("div",M,[a(r,{class:"whitespace-nowrap",disabled:!m.value,onClick:C},{default:g(()=>e[8]||(e[8]=[w(" Remove ")])),_:1},8,["disabled"])])]),l("div",N,[a(c,{class:"w-full md:w-96",items:s.tags.size,nullable:!0,placeholder:"Size",modelValue:v.value,"onUpdate:modelValue":e[3]||(e[3]=t=>v.value=t)},null,8,["items","modelValue"]),l("div",$,[a(r,{class:"whitespace-nowrap",disabled:!v.value,onClick:_},{default:g(()=>e[9]||(e[9]=[w(" Remove ")])),_:1},8,["disabled"])])]),l("div",j,[a(c,{class:"w-full md:w-96",items:s.tags.state,nullable:!0,placeholder:"State",modelValue:i.value,"onUpdate:modelValue":e[4]||(e[4]=t=>i.value=t)},null,8,["items","modelValue"]),l("div",y,[a(r,{class:"whitespace-nowrap",disabled:!i.value,onClick:x},{default:g(()=>e[10]||(e[10]=[w(" Remove ")])),_:1},8,["disabled"])])]),l("div",O,[a(c,{class:"w-full md:w-96",items:s.tags.event,nullable:!0,placeholder:"Event",modelValue:n.value,"onUpdate:modelValue":e[5]||(e[5]=t=>n.value=t)},null,8,["items","modelValue"]),l("div",q,[a(r,{class:"whitespace-nowrap",disabled:!n.value,onClick:T},{default:g(()=>e[11]||(e[11]=[w(" Remove ")])),_:1},8,["disabled"])])])]))}};export{H as default};
