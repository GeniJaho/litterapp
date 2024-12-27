import B from"./SimpleTagShortcutItem-Bp4iMjuy.js";import{r as w,k as C,z,o,c as y,w as d,a as l,b as n,u as a,M as x,d as i,P as S,f as L,e as v,F as k,g as V,n as m,t as A}from"./app-CZNMuXyv.js";import{_ as F}from"./PrimaryButton-Dv3cuahV.js";import{n as M,p as N,S as T,q as $,r as j,v as q}from"./TagBox-D1H7U3OL.js";const P={class:"relative"},_={class:"relative"},D={key:0,class:"relative cursor-default select-none"},E={class:"px-3 py-4"},I={class:"grid grid-cols-1 gap-3 max-h-36 md:max-h-144 overflow-y-auto"},J={__name:"TagShortcutBox",props:{items:Array,modelValue:Object,autofocus:Boolean,nullable:{type:Boolean,default:!1},placeholder:{type:String,default:""},layout:{type:String,default:"default"}},emits:["update:modelValue"],setup(s){const c=s;let r=w(""),h=C(()=>r.value===""?c.items:c.items.filter(u=>u.shortcut.toLowerCase().replace(/\s+/g,"").includes(r.value.toLowerCase().replace(/\s+/g,""))));const b=w(null);return z(()=>{c.autofocus&&setTimeout(()=>{var u;return(u=b.value.el)==null?void 0:u.focus()},300)}),(u,t)=>(o(),y(a(q),{modelValue:s.modelValue,"onUpdate:modelValue":t[3]||(t[3]=f=>u.$emit("update:modelValue",f)),nullable:s.nullable,by:"id"},{default:d(({activeOption:f})=>[l("div",P,[l("div",_,[n(a(M),{ref_key:"input",ref:b,class:"w-full rounded-md border-0 bg-white dark:bg-gray-900 py-1.5 pl-3 pr-12 text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm sm:leading-6",displayValue:e=>e==null?void 0:e.shortcut,placeholder:s.placeholder,onChange:t[0]||(t[0]=e=>x(r)?r.value=e.target.value:r=e.target.value),onFocus:t[1]||(t[1]=e=>e.target.select()),autocomplete:"off"},null,8,["displayValue","placeholder"]),n(a(N),{class:"absolute inset-y-0 right-0 flex items-center pr-2"},{default:d(()=>t[4]||(t[4]=[l("svg",{class:"h-5 w-5 text-gray-400",viewBox:"0 0 20 20",fill:"currentColor","aria-hidden":"true"},[l("path",{"fill-rule":"evenodd",d:"M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z","clip-rule":"evenodd"})],-1)])),_:1})]),n(a(T),{leave:"transition ease-in duration-100",leaveFrom:"opacity-100",leaveTo:"opacity-0",onAfterLeave:t[2]||(t[2]=e=>x(r)?r.value="":r="")},{default:d(()=>[n(a($),{class:"absolute z-10 mt-1 max-h-96 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"},{default:d(()=>[a(h).length===0?(o(),i("div",D,[n(a(S),{href:u.route("tag-shortcuts.index"),class:"flex justify-center"},{default:d(()=>[n(F,{class:"whitespace-nowrap"},{default:d(()=>t[5]||(t[5]=[L(" Add Shortcut ")])),_:1})]),_:1},8,["href"])])):v("",!0),(o(!0),i(k,null,V(a(h),e=>(o(),y(a(j),{as:"template",key:e.id,value:e},{default:d(({selected:p,active:g})=>[l("li",{class:m(["relative cursor-default select-none py-2 pl-10 pr-4",{"bg-indigo-600 text-white":g,"text-gray-900":!g}])},[l("span",{class:m(["block truncate",{"font-medium":p,"font-normal":!p}])},A(e.shortcut),3),p?(o(),i("span",{key:0,class:m(["absolute inset-y-0 left-0 flex items-center pl-3",{"text-white":g,"text-indigo-600":!g}])},t[6]||(t[6]=[l("svg",{class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor","aria-hidden":"true"},[l("path",{"fill-rule":"evenodd",d:"M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z","clip-rule":"evenodd"})],-1)]),2)):v("",!0)],2)]),_:2},1032,["value"]))),128))]),_:1})]),_:1}),f?(o(),i("div",{key:0,class:m(["fixed md:absolute bottom-0 md:top-0 transform transition-all md:min-w-96 z-10",{"min-w-full md:right-full":s.layout==="default","min-w-64 md:left-full":s.layout==="bulk"}])},[l("div",{class:m(["mb-4 md:mb-0 bg-white/50 dark:bg-gray-800/30 backdrop-blur-xl rounded-lg shadow-xl overflow-hidden",{"mr-8 sm:mr-20 md:mr-4":s.layout==="default","md:ml-4":s.layout==="bulk"}])},[l("div",E,[l("div",I,[(o(!0),i(k,null,V(f.tag_shortcut_items,e=>(o(),y(B,{key:e.id,item:e},null,8,["item"]))),128))])])],2)],2)):v("",!0)])]),_:1},8,["modelValue","nullable"]))}};export{J as _};
