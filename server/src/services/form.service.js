const { z } = require("zod");
const { supabaseAdmin } = require("../config/supabase");
const { sendMail } = require("./mail.service");
const env = require("../config/env");

const baseSchema = z.object({
  name: z.string().trim().min(1),
  email: z.string().trim().email(),
  phone: z.string().trim().min(1).optional().default(""),
  form_type: z.string().trim().min(1)
});

const contactSchema = baseSchema.extend({
  organization: z.string().trim().min(1),
  organization_type: z.string().trim().optional().default(""),
  challenges: z.string().trim().optional().default("")
});

const prosperaSchema = baseSchema.extend({
  company: z.string().trim().min(1),
  service: z.string().trim().min(1),
  message: z.string().trim().optional().default("")
});

const mosaicSchema = baseSchema.extend({
  organization: z.string().trim().min(1),
  title: z.string().trim().min(1),
  organization_type: z.string().trim().min(1),
  challenges: z.string().trim().optional().default(""),
  interest: z.array(z.string().trim().min(1)).min(1)
});

const sportsSchema = baseSchema.extend({
  organization: z.string().trim().min(1),
  organization_type: z.string().trim().min(1),
  sport_type: z.string().trim().min(1),
  challenges: z.string().trim().optional().default("")
});

const financingSchema = z.object({
  form_type: z.string().trim().min(1),
  name: z.string().trim().optional().default(""),
  email: z.string().trim().email(),
  phone: z.string().trim().optional().default(""),
  legal_business_name: z.string().trim().optional().default(""),
  dba: z.string().trim().optional().default(""),
  business_type: z.string().trim().optional().default(""),
  physical_address: z.string().trim().optional().default(""),
  city_state_zip: z.string().trim().optional().default(""),
  fax: z.string().trim().optional().default(""),
  fed_tax_id: z.string().trim().optional().default(""),
  email_communication: z.string().trim().optional().default(""),
  legal_structure: z.string().trim().optional().default(""),
  num_employees: z.string().trim().optional().default(""),
  date_business_started: z.string().trim().optional().default(""),
  date_became_owner: z.string().trim().optional().default(""),
  owner1_name_title: z.string().trim().optional().default(""),
  owner1_home_address: z.string().trim().optional().default(""),
  owner1_home_phone: z.string().trim().optional().default(""),
  owner1_cell: z.string().trim().optional().default(""),
  owner1_ssn: z.string().trim().optional().default(""),
  owner1_dob: z.string().trim().optional().default(""),
  owner1_ownership_percent: z.string().trim().optional().default(""),
  owner2_name_title: z.string().trim().optional().default(""),
  owner2_home_address: z.string().trim().optional().default(""),
  owner2_home_phone: z.string().trim().optional().default(""),
  owner2_cell: z.string().trim().optional().default(""),
  owner2_ssn: z.string().trim().optional().default(""),
  owner2_dob: z.string().trim().optional().default(""),
  owner2_ownership_percent: z.string().trim().optional().default(""),
  bank_name: z.string().trim().optional().default(""),
  bank_account: z.string().trim().optional().default(""),
  bank_phone: z.string().trim().optional().default(""),
  bank_contact: z.string().trim().optional().default(""),
  trade_ref_1: z.string().trim().optional().default(""),
  trade_ref_1_phone: z.string().trim().optional().default(""),
  trade_ref_2: z.string().trim().optional().default(""),
  trade_ref_2_phone: z.string().trim().optional().default(""),
  vendor_name: z.string().trim().optional().default(""),
  vendor_phone: z.string().trim().optional().default(""),
  sales_person: z.string().trim().optional().default(""),
  equipment_description: z.string().trim().optional().default(""),
  equipment_new_used: z.string().trim().optional().default(""),
  equipment_price: z.string().trim().optional().default(""),
  equipment_term: z.string().trim().optional().default(""),
  signature1_name: z.string().trim().optional().default(""),
  signature1_title: z.string().trim().optional().default(""),
  signature1_date: z.string().trim().optional().default(""),
  signature2_name: z.string().trim().optional().default(""),
  signature2_title: z.string().trim().optional().default(""),
  signature2_date: z.string().trim().optional().default("")
});

function parsePayload(schema, payload) {
  const parsed = schema.safeParse(payload);

  if (!parsed.success) {
    const details = parsed.error.issues.map((issue) => ({
      field: issue.path.join("."),
      message: issue.message
    }));

    const error = new Error("Validation failed");
    error.statusCode = 400;
    error.details = details;
    throw error;
  }

  return parsed.data;
}

function baseSubmissionMeta(req, formType) {
  return {
    form_type: formType,
    ip_address: req.headers["x-forwarded-for"]?.split(",")[0]?.trim() || req.ip || "",
    user_agent: req.get("user-agent") || "",
    status: "new"
  };
}

async function insertSubmission(payload) {
  const { data, error } = await supabaseAdmin
    .from("contact_forms")
    .insert(payload)
    .select("id")
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return data;
}

async function sendNotificationEmails({ formType, email, name, summary }) {
  const userSubject = `Thank you for your ${formType} inquiry`;
  const adminSubject = `New ${formType} inquiry received`;

  await Promise.allSettled([
    sendMail({
      to: email,
      subject: userSubject,
      html: `<p>Hi ${name || "there"},</p><p>We received your ${formType} inquiry and will get back to you shortly.</p>`,
      text: `Hi ${name || "there"}, we received your ${formType} inquiry and will get back to you shortly.`
    }),
    env.adminEmail
      ? sendMail({
          to: env.adminEmail,
          subject: adminSubject,
          html: `<p>A new ${formType} inquiry has been submitted.</p><pre>${summary}</pre>`,
          text: `A new ${formType} inquiry has been submitted.\n\n${summary}`
        })
      : Promise.resolve({ skipped: true })
  ]);
}

async function submitContact(req) {
  const input = parsePayload(contactSchema, req.body);
  const record = {
    ...baseSubmissionMeta(req, input.form_type),
    name: input.name,
    email: input.email,
    phone: input.phone,
    organization: input.organization,
    company: input.organization,
    organization_type: input.organization_type,
    goals_challenges: input.challenges,
    message: input.challenges,
    form_data: {
      organization: input.organization,
      organization_type: input.organization_type,
      challenges: input.challenges
    }
  };

  const created = await insertSubmission(record);
  await sendNotificationEmails({
    formType: input.form_type,
    email: input.email,
    name: input.name,
    summary: JSON.stringify(record, null, 2)
  });

  return created;
}

async function submitProspera(req) {
  const input = parsePayload(prosperaSchema, req.body);
  const record = {
    ...baseSubmissionMeta(req, input.form_type),
    name: input.name,
    email: input.email,
    phone: input.phone,
    company: input.company,
    service: input.service,
    message: input.message,
    form_data: {
      company: input.company,
      service: input.service,
      message: input.message
    }
  };

  const created = await insertSubmission(record);
  await sendNotificationEmails({
    formType: input.form_type,
    email: input.email,
    name: input.name,
    summary: JSON.stringify(record, null, 2)
  });

  return created;
}

async function submitMosaic(req) {
  const rawInterest = Array.isArray(req.body.interest)
    ? req.body.interest
    : req.body["interest[]"];
  const input = parsePayload(mosaicSchema, {
    ...req.body,
    interest: Array.isArray(rawInterest)
      ? rawInterest
      : rawInterest
        ? [rawInterest]
        : []
  });

  const record = {
    ...baseSubmissionMeta(req, input.form_type),
    name: input.name,
    email: input.email,
    phone: input.phone,
    organization: input.organization,
    company: input.organization,
    organization_type: input.organization_type,
    job_title: input.title,
    interests: input.interest,
    goals_challenges: input.challenges,
    message: input.challenges,
    form_data: {
      organization: input.organization,
      title: input.title,
      organization_type: input.organization_type,
      interest: input.interest,
      challenges: input.challenges
    }
  };

  const created = await insertSubmission(record);
  await sendNotificationEmails({
    formType: input.form_type,
    email: input.email,
    name: input.name,
    summary: JSON.stringify(record, null, 2)
  });

  return created;
}

async function submitSports(req) {
  const input = parsePayload(sportsSchema, {
    ...req.body,
    form_type: req.body.form_type || "sports"
  });

  const record = {
    ...baseSubmissionMeta(req, input.form_type),
    name: input.name,
    email: input.email,
    phone: input.phone,
    organization: input.organization,
    company: input.organization,
    organization_type: input.organization_type,
    service: input.sport_type,
    message: input.challenges,
    form_data: {
      organization: input.organization,
      organization_type: input.organization_type,
      sport_type: input.sport_type,
      challenges: input.challenges
    }
  };

  const created = await insertSubmission(record);
  await sendNotificationEmails({
    formType: input.form_type,
    email: input.email,
    name: input.name,
    summary: JSON.stringify(record, null, 2)
  });

  return created;
}

async function submitFinancing(req) {
  const input = parsePayload(financingSchema, req.body);
  const record = {
    ...baseSubmissionMeta(req, input.form_type),
    name: input.name || input.signature1_name || "",
    email: input.email,
    phone: input.phone,
    company: input.legal_business_name,
    organization: input.legal_business_name,
    form_data: input
  };

  const created = await insertSubmission(record);
  await sendNotificationEmails({
    formType: input.form_type,
    email: input.email,
    name: record.name,
    summary: JSON.stringify(record, null, 2)
  });

  return created;
}

module.exports = {
  submitContact,
  submitProspera,
  submitMosaic,
  submitSports,
  submitFinancing
};
