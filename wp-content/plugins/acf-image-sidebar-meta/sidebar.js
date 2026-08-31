"use strict";
(() => {
  // src/wp-shim.js
  var addFilter = window.wp.hooks.addFilter;
  var createHigherOrderComponent = window.wp.compose.createHigherOrderComponent;
  var InspectorControls = window.wp.blockEditor.InspectorControls;
  var PanelBody = window.wp.components.PanelBody;
  var TextControl = window.wp.components.TextControl;
  var SelectControl = window.wp.components.SelectControl;
  var useState = window.wp.element.useState;
  var useEffect = window.wp.element.useEffect;

  // src/sidebar.jsx
  var imageMetaCache = {};
  var withImageMetaPanel = createHigherOrderComponent((BlockEdit) => (props) => {
    const { name, attributes } = props;
    const imageId = name === "core/image" ? attributes.id : null;
    const [authorSource, setAuthorSource] = useState("");
    const [authorId, setAuthorId] = useState("");
    const [authors, setAuthors] = useState([]);
    const [, setMetaLoaded] = useState(false);
    useEffect(() => {
      console.log("create");
      fetch("/wp-admin/admin-ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ action: "get_authors_list" })
      }).then((res) => res.json()).then((data) => setAuthors(
        (data.data || []).map((a) => ({
          id: String(a.id),
          title: a.title
        }))
      )).catch(() => setAuthors([]));
    }, []);
    useEffect(() => {
      if (!imageId) return;
      console.log("imageId ", imageId);
      if (imageMetaCache[imageId]) {
        const meta = imageMetaCache[imageId];
        setAuthorSource(meta.attached_image_author_source || "");
        setAuthorId(String(meta.attached_image_author_id || ""));
        setMetaLoaded(true);
        return;
      }
      fetch("/wp-admin/admin-ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "get_image_meta",
          attachment_id: String(imageId)
        })
      }).then((res) => res.json()).then((data) => {
        const meta = data.data || {};
        imageMetaCache[imageId] = meta;
        setAuthorSource(meta.attached_image_author_source || "");
        setAuthorId(String(meta.attached_image_author_id || ""));
        setMetaLoaded(true);
      });
    }, [imageId]);
    const saveField = (field, value) => {
      if (!imageId) return;
      console.log("saveField ", saveField);
      const payload = {
        action: "update_image_meta",
        attachment_id: String(imageId),
        author_source: field === "attached_image_author_source" ? value : authorSource,
        author_id: field === "attached_image_author_id" ? value : authorId
      };
      fetch("/wp-admin/admin-ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(payload)
      });
    };
    if (name !== "core/image" || !imageId) {
      return /* @__PURE__ */ wp.element.createElement(BlockEdit, { ...props });
    }
    return /* @__PURE__ */ wp.element.createElement(React.Fragment, null, /* @__PURE__ */ wp.element.createElement(BlockEdit, { ...props }), /* @__PURE__ */ wp.element.createElement(InspectorControls, null, /* @__PURE__ */ wp.element.createElement(PanelBody, { title: "Image ACF Metadata", initialOpen: true }, /* @__PURE__ */ wp.element.createElement(
      TextControl,
      {
        label: "Image Source",
        value: authorSource,
        onChange: (value) => {
          setAuthorSource(value);
          saveField("attached_image_author_source", value);
        }
      }
    ), /* @__PURE__ */ wp.element.createElement(
      SelectControl,
      {
        label: "Author",
        value: authorId,
        options: [
          { label: "Select an author", value: "" },
          ...authors.map((author) => ({
            label: author.title,
            value: author.id
          }))
        ],
        onChange: (value) => {
          setAuthorId(value);
          saveField("attached_image_author_id", value);
        }
      }
    ))));
  }, "withImageMetaPanel");
  addFilter(
    "editor.BlockEdit",
    "my-plugin/with-image-meta-panel",
    withImageMetaPanel
  );
})();
