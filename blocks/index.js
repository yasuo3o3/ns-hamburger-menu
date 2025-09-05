( function( wp ) {
  const { registerBlockType } = wp.blocks;
  const { InspectorControls, InnerBlocks } = wp.blockEditor || wp.editor;
  const {
    PanelBody, RangeControl, ToggleControl,
    __experimentalNumberControl: NumberControl,
    TextControl, RadioControl
  } = wp.components;
  const { createElement: h, Fragment } = wp.element;

  /* ========== 親: ns/hamburger ========== */
  const Title = () => h('div', { style:{padding:'12px',border:'1px dashed #ccc',borderRadius:'8px',background:'#fafafa'} },
    h('strong', null, 'NS Hamburger Menu'),
    h('div', { style:{marginTop:'6px',opacity:.8} }, 'フロントでは UL の上下にスロット内容が差し込まれます')
  );

  registerBlockType('ns/hamburger', {
    title: 'NS Hamburger Menu',
    icon: 'menu',
    category: 'design',
    attributes: {
      columns: { type:'number', default: null },
      topFontPx: { type:'number', default: null },
      subFontPx: { type:'number', default: null },
      colorStart: { type:'string', default: null },
      colorEnd: { type:'string', default: null },
      hueAnim: { type:'boolean', default: null },
      hueSpeedSec: { type:'number', default: null },
      zIndex: { type:'number', default: null }
    },
    edit: (props) => {
      const { attributes, setAttributes } = props;
      const setNum = (k) => (v)=> setAttributes({ [k]: (v===""||v===undefined)? null : Number(v) });
      const setStr = (k) => (v)=> setAttributes({ [k]: v || null });

      const template = [
        ['ns/hamburger-slot', { position: 'before' }],
        ['ns/hamburger-slot', { position: 'after' }]
      ];

      return h(Fragment, null,
        h(InspectorControls, null,
          h(PanelBody, { title:'表示設定（未入力はプラグイン既定を使用）', initialOpen:true },
            h(RangeControl, {
              label:'列数（1〜6）', min:1, max:6, allowReset:true, value: attributes.columns,
              onChange: (v)=> setAttributes({ columns: v ?? null })
            }),
            h(NumberControl, { label:'親の文字サイズ(px)', value: attributes.topFontPx, min:10, onChange: setNum('topFontPx') }),
            h(NumberControl, { label:'子の文字サイズ(px)', value: attributes.subFontPx, min:8,  onChange: setNum('subFontPx') }),
            h(TextControl, { label:'開始色（#0ea5e9 など）', value: attributes.colorStart || '', onChange: setStr('colorStart') }),
            h(TextControl, { label:'終了色（#a78bfa など）', value: attributes.colorEnd || '', onChange: setStr('colorEnd') }),
            h(ToggleControl, { label:'色相アニメON', checked: attributes.hueAnim ?? undefined, onChange: (v)=> setAttributes({ hueAnim: v }) }),
            h(NumberControl, { label:'色相アニメ速度(秒/周)', value: attributes.hueSpeedSec, min:3, onChange: setNum('hueSpeedSec') }),
            h(NumberControl, { label:'Z-index', value: attributes.zIndex, min:1000, onChange: setNum('zIndex') })
          )
        ),
        h(Title),
        h('div', { style:{marginTop:'8px',padding:'8px 10px',background:'#fff',border:'1px solid #e5e7eb',borderRadius:'8px'} },
          h('div', { style:{fontSize:12,opacity:.7,marginBottom:6} }, 'ここに「上部/下部スロット」を追加して、任意のブロックを入れられます'),
          h(InnerBlocks, { allowedBlocks:['ns/hamburger-slot'], template, templateLock:false })
        )
      );
    },
    // 親は「子の内容」を保存する
    save: () => h( (wp.blockEditor || wp.editor).InnerBlocks.Content )
  });

  /* ========== 子: ns/hamburger-slot（ULの上/下） ========== */
  registerBlockType('ns/hamburger-slot', {
    title: 'NS Hamburger Slot',
    icon: 'insert',
    category: 'design',
    parent: ['ns/hamburger'],
    attributes: {
      position: { type:'string', default:'before' } // 'before' | 'after'
    },
    supports: { reusable: false },
    edit: (props) => {
      const { attributes, setAttributes } = props;
      const pos = attributes.position || 'before';
      return h(Fragment, null,
        h(InspectorControls, null,
          h(PanelBody, { title:'スロット位置', initialOpen:true },
            h(RadioControl, {
              label:'UL との位置',
              selected: pos,
              options: [
                { label:'上（before）', value:'before' },
                { label:'下（after）',  value:'after' }
              ],
              onChange: (v)=> setAttributes({ position: v || 'before' })
            })
          )
        ),
        h('div', { style:{padding:'10px',border:'1px dashed #cbd5e1',borderRadius:'8px',background:'#f8fafc'} },
          h('div', { style:{fontSize:12,opacity:.65,marginBottom:6} }, `この中身が「${pos==='after'?'UL の下':'UL の上'}」に出ます`),
          h(InnerBlocks)
        )
      );
    },
    // 子は“枠”を保存＝ position 属性をサーバー側に渡す
    save: () => null
  });

} )( window.wp );
