import{C as y,k as p,d,b as t,u as o,w as s,F as x,o as f,h as k,a as r,e as v,n as h,f as n,P as u,i as b}from"./app-CZNMuXyv.js";import{A as _}from"./AuthenticationCard-DHUNqPNt.js";import{_ as w}from"./AuthenticationCardLogo-CCKuotW1.js";import{_ as V}from"./PrimaryButton-Dv3cuahV.js";import"./_plugin-vue_export-helper-DlAUqK2U.js";const C={key:0,class:"mb-4 font-medium text-sm text-green-600 dark:text-green-400"},E={class:"mt-4 flex items-center justify-between"},j={__name:"VerifyEmail",props:{status:String},setup(l){const m=l,i=y({}),c=()=>{i.post(route("verification.send"))},g=p(()=>m.status==="verification-link-sent");return(a,e)=>(f(),d(x,null,[t(o(k),{title:"Email Verification"}),t(_,null,{logo:s(()=>[t(w)]),default:s(()=>[e[3]||(e[3]=r("div",{class:"mb-4 text-sm text-gray-600 dark:text-gray-400"}," Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another. ",-1)),g.value?(f(),d("div",C," A new verification link has been sent to the email address you provided in your profile settings. ")):v("",!0),r("form",{onSubmit:b(c,["prevent"])},[r("div",E,[t(V,{class:h({"opacity-25":o(i).processing}),disabled:o(i).processing},{default:s(()=>e[0]||(e[0]=[n(" Resend Verification Email ")])),_:1},8,["class","disabled"]),r("div",null,[t(o(u),{href:a.route("profile.show"),class:"underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"},{default:s(()=>e[1]||(e[1]=[n(" Edit Profile")])),_:1},8,["href"]),t(o(u),{href:a.route("logout"),method:"post",as:"button",class:"underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 ml-2"},{default:s(()=>e[2]||(e[2]=[n(" Log Out ")])),_:1},8,["href"])])])],32)]),_:1})],64))}};export{j as default};
