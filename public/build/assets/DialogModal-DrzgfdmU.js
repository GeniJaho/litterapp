import{_ as i}from"./Modal-B-odY7TB.js";import{o as r,c as d,w as n,a as t,p as o}from"./app-CZNMuXyv.js";const m={class:"px-6 py-4"},x={class:"text-lg font-medium text-gray-900 dark:text-gray-100"},h={class:"mt-4 text-sm text-gray-600 dark:text-gray-400"},f={class:"flex flex-row justify-end px-6 py-4 bg-gray-100 dark:bg-gray-800 text-right"},p={__name:"DialogModal",props:{show:{type:Boolean,default:!1},maxWidth:{type:String,default:"2xl"},closeable:{type:Boolean,default:!0}},emits:["close"],setup(e,{emit:a}){const l=a,c=()=>{l("close")};return(s,g)=>(r(),d(i,{show:e.show,"max-width":e.maxWidth,closeable:e.closeable,onClose:c},{default:n(()=>[t("div",m,[t("div",x,[o(s.$slots,"title")]),t("div",h,[o(s.$slots,"content")])]),t("div",f,[o(s.$slots,"footer")])]),_:3},8,["show","max-width","closeable"]))}};export{p as _};
